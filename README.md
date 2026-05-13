# Test API — Purchase & Sales System

REST API для учёта партионной закупки, продажи (FIFO) и возвратов товаров.

**Stack:** Laravel 13 · PHP 8.4 · PostgreSQL 16 · Redis 7 · Nginx · Docker · Laravel Sanctum · spatie/laravel-permission

---

## Содержание

- [Доменная модель](#доменная-модель)
- [Архитектура](#архитектура)
- [Запуск](#запуск)
- [Авторизация и RBAC](#авторизация-и-rbac)
- [API endpoints](#api-endpoints)
- [Складской учёт и FIFO](#складской-учёт-и-fifo)
- [Возвраты](#возвраты)
- [Прибыль по партии](#прибыль-по-партии)
- [Тесты](#тесты)
- [Структура проекта](#структура-проекта)

---

## Доменная модель

| Сущность | Назначение |
|---|---|
| `users` (type: `client` / `admin` / `manager` / `employee`) | Унифицированная таблица пользователей; заказы создаются от имени `client`. |
| `providers` | Поставщики. |
| `categories` | Иерархия категорий (parent-child через `parent_id`). |
| `products` | Товары, привязаны к категории. |
| `storages` | Склады. |
| `batches` + `batch_items` | Партии закупок. `batch_items.available_qty` — generated column в Postgres. |
| `stock_movements` | Append-only журнал движений (source of truth). |
| `storage_stocks` | Денормализованный текущий остаток (read-model). |
| `orders` + `order_items` + `order_item_allocations` | Заказы. Allocations связывают каждую проданную единицу с конкретной партией. |
| `purchase_refunds` (+ items) | Возврат поставщику. |
| `client_refunds` (+ items) | Возврат от клиента; `order_item_allocation_id` обязателен — нужен для корректной прибыли. |

### Ключевые инварианты на уровне БД

- `batch_items.available_qty = qty_purchased - qty_refunded_to_provider - qty_sold + qty_returned_by_clients` (GENERATED STORED).
- CHECK `qty_refunded_to_provider + qty_sold - qty_returned_by_clients <= qty_purchased` — последняя линия защиты от overselling.
- CHECK по всем `qty >= 0` и `price >= 0`.
- Все статусы (`batches`, `orders`, `refunds`, `stock_movements.type`) ограничены CHECK-constraint, дополненным PHP enum.

---

## Архитектура

```
HTTP layer
 ├─ Legacy Controllers тонкие, маршрутизация → сервис → Resource
 ├─ Admin Controllers  thin controllers: Request -> ActionData -> Action -> Presenter
 ├─ FormRequests / ActionData validation
 └─ Resources / Presenters

Application layer
 ├─ Services           бизнес-операции (DB::transaction + lockForUpdate)
 └─ DTO                типизированные payloads

Domain layer
 ├─ Models             Eloquent
 ├─ Enums              UserType, BatchStatus, OrderStatus, RefundStatus, StockMovementType
 └─ Exceptions         доменные ошибки с собственным HTTP-рендерером (409/422)
```

Принципы:

- Бизнес-логика **только** в сервисах, контроллер ≤ 5 строк.
- Каждая запись в >1 таблицу обёрнута в `DB::transaction()`.
- FIFO и возвраты держат `lockForUpdate` на `batch_items` / `order_items` — без этого возможен overselling.
- Frontend **никогда** не передаёт `batch_id` — это бизнес-инвариант, его выбирает сервер.
- Для модульных admin endpoints используется envelope:
  - `{ "success": true, "result": ... }`
  - `{ "success": false, "error": {...}, "result": null }`

---

## Запуск

### Требования
- Docker + Docker Compose
- Порты 8080 и 6379 свободны на хосте

### Подъём

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

API будет доступен на `http://localhost:8080`.

### Полезные команды

```bash
# Тесты
docker compose exec app php artisan test

# Свежая миграция с демо-данными
docker compose exec app php artisan migrate:fresh --seed --force

# Логи приложения
docker compose logs -f app

# Tinker
docker compose exec app php artisan tinker

# Swagger UI
open http://localhost:8080/swagger

# Стоп
docker compose down

# Стоп с удалением volume Postgres
docker compose down -v
```

### .env

- `DB_HOST=postgres` — hostname контейнера, не `localhost`.
- `REDIS_HOST=redis` — аналогично.
- Postgres-данные персистятся в named volume `pgdata`. Извне доступен на порту `5433`.

---

## Авторизация и RBAC

- Все защищённые эндпоинты требуют Bearer токен (`Authorization: Bearer <token>`).
- Токены выдаются через:
  - `POST /api/v1/auth/admin/login`
  - `POST /api/v1/auth/client/login`
  - `POST /api/v1/auth/login` (legacy compatibility)
  и хранятся через **Laravel Sanctum**.
- Роли и права — **spatie/laravel-permission**, guard `sanctum`.

### Системные роли (создаются сеедером)

| Роль | Права |
|---|---|
| `admin` | все |
| `client` | может покупать и продавать (client-orders + purchases + related views/create/update) |
| `manager` | работа с клиентами, заказами, возвратами клиентов |
| `accountant` | просмотр закупок, заказов, прибыли, остатков |
| `warehouse_manager` | работа с поставщиками, товарами, складами, закупками, возвратами поставщику |

### Default admin

`RolesAndPermissionsSeeder` идемпотентно создаёт:

```
email:    admin@example.com
password: password
role:     admin
```

Логин:

```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}'
```

В ответ — `token`, который надо подставлять в `Authorization: Bearer ...`.

---

## API endpoints

Все эндпоинты под префиксом `/api/v1`. Базовый формат ответа — `{ "data": ... }`.

### Соответствие ТЗ (важно для проверки)

Ниже маппинг между формулировкой из тестового задания и фактическими роутами проекта.

| ТЗ endpoint | Реализовано в проекте |
|---|---|
| `POST /api/v1/auth/register` | `POST /api/v1/auth/register` |
| `POST /api/v1/auth/login` | `POST /api/v1/auth/login` (+ `admin/login`, `client/login`) |
| `POST /api/v1/auth/logout` | `POST /api/v1/auth/logout` |
| `GET /api/v1/auth/me` | `GET /api/v1/auth/me` |
| `POST /api/v1/purchases` | `POST /api/v1/purchases` |
| `POST /api/v1/batches/refund` | `POST /api/v1/provider-refunds` |
| `GET /api/v1/products/available` | `GET /api/v1/products/available` |
| `POST /api/v1/client-orders` | `POST /api/v1/client-orders` |
| `POST /api/v1/client-orders/refund` | `POST /api/v1/client-refunds` |
| `GET /api/v1/storages/remaining-quantities` | `GET /api/v1/storages/remaining-quantities` |
| `GET /api/v1/batches/profit` | `GET /api/v1/batches/profit` |

Пояснение:
- Логика по ТЗ сохранена полностью (FIFO, возвраты, прибыль, остатки, RBAC, транзакции, lockForUpdate).
- Для двух операций возвратов использованы эквивалентные роуты с другим именованием:
  - `provider-refunds` вместо `batches/refund`
  - `client-refunds` вместо `client-orders/refund`
- Это сделано без изменения архитектурного подхода и без дублирования контрактов.

### Auth (открытые)

| Метод | Путь | Назначение |
|---|---|---|
| POST | `/api/v1/auth/register` | регистрация (выдаёт токен) |
| POST | `/api/v1/auth/login` | вход (legacy) |
| POST | `/api/v1/auth/admin/login` | вход только для `admin` |
| POST | `/api/v1/auth/client/login` | вход только для `client` |

### Auth (требуют токен)

| Метод | Путь | Назначение |
|---|---|---|
| POST | `/api/v1/auth/logout` | удалить текущий токен |
| GET | `/api/v1/auth/me` | данные текущего пользователя |
| POST | `/api/v1/auth/client/company-create` | создать company (provider) и привязать к client |

### Admin modular endpoints

- `/api/v1/admin/category/{create,index,show,update,delete,item-list}`
- `/api/v1/admin/provider/{create,index,show,update,delete,item-list}`
- `/api/v1/admin/product/{create,index,show,update,delete,item-list}`
- `/api/v1/admin/file/{create,index,show,update,delete,item-list}`

### Swagger

- UI: `/swagger`
- OpenAPI spec: `/docs/openapi.yaml`

### Управление пользователями и ролями

| Метод | Путь | Permission |
|---|---|---|
| GET/POST/GET/PUT/DELETE | `/api/v1/users[/{id}]` | `users.view`/`users.create`/`users.update`/`users.delete` |
| GET | `/api/v1/roles` | `roles.view` |
| GET | `/api/v1/permissions` | `roles.view` |
| POST | `/api/v1/users/{user}/roles` | `roles.assign` |

### Справочники (CRUD)

| Сущность | Префикс | Permissions |
|---|---|---|
| Поставщики | `/api/v1/providers` | `providers.{view,create,update,delete}` |
| Клиенты | `/api/v1/clients` | `clients.{view,create,update,delete}` |
| Категории | `/api/v1/categories` | `categories.{view,create,update,delete}` |
| Товары | `/api/v1/products` | `products.{view,create,update,delete}` |
| Склады | `/api/v1/storages` | `storages.{view,create,update,delete}` |

Удаление физически запрещено, когда есть связанные данные (партии/заказы/остатки/дочерние записи) — возврат **409 RelationConflict**.

### Бизнес endpoints

### `POST /api/v1/purchases` — закупка партии (`purchases.create`)

```json
{
  "provider_id": 1,
  "storage_id": 1,
  "purchased_at": "2026-05-12T10:00:00Z",
  "items": [
    { "product_id": 1, "qty": 100, "purchase_price": "50.00", "sale_price": "80.00" }
  ]
}
```
**201 Created** — создаётся `batch` + `batch_items` + `stock_movements(type=purchase)` + инкремент `storage_stocks`.

### `POST /api/v1/client-orders` — заказ клиента (FIFO) (`client_orders.create`)

```json
{
  "user_id": 1,
  "ordered_at": "2026-05-12T11:00:00Z",
  "products": [{ "id": 1, "qty": 15 }]
}
```
- Frontend передаёт **только** `user_id` и список `products`.
- Backend делает FIFO-распределение по самым старым партиям с `lockForUpdate`.
- При нехватке остатка — **409** `{"error":"insufficient_stock","requested":...,"available":...}`.
- Принимается также `client_id` как алиас к `user_id` (обратная совместимость).

### `POST /api/v1/provider-refunds` — возврат поставщику (`purchases.refund`)

```json
{
  "batch_id": 5,
  "refunded_at": "2026-05-12T12:00:00Z",
  "reason": "defective",
  "items": [{ "batch_item_id": 33, "qty": 10 }]
}
```
- Списывает товар со склада, инкрементит `batch_items.qty_refunded_to_provider`.
- Нельзя превысить `available_qty` (т.е. вернуть проданный товар) → **409** `refund_exceeds_available`.

### `POST /api/v1/client-refunds` — возврат от клиента (`client_orders.refund`)

```json
{
  "order_id": 7,
  "refunded_at": "2026-05-12T13:00:00Z",
  "reason": "client changed mind",
  "items": [{ "order_item_id": 17, "qty": 2 }]
}
```
- Распределяет возврат по `order_item_allocations` (FIFO по allocation id).
- Каждая запись `client_refund_items` ссылается на `order_item_allocation_id` — это важно для расчёта прибыли партии.
- Нельзя вернуть больше, чем продано (минус уже возвращено) → **409** `refund_exceeds_sold`.

### `GET /api/v1/products/available` (`products.view`)

Список товаров с положительным остатком.

```json
{ "data": [
  { "id": 1, "name": "Mouse", "category_name": "Peripherals", "price": "85.00", "qty": 17 }
]}
```
`price` — `sale_price` самой старой доступной партии (FIFO-prefix).

### `GET /api/v1/storages/remaining-quantities?date=YYYY-MM-DD&storage_id=` (`reports.stock_remaining`)

Остатки на конец указанной даты. Считается через `stock_movements` (`SUM(qty * direction)`).

### `GET /api/v1/batches/profit?from=&to=&batch_id=&provider_id=` (`reports.batch_profit`)

```json
{ "data": [{
  "batch_id": 1, "code": "BATCH-...", "provider_name": "Acme",
  "purchase_cost_for_sold": "490.00",
  "gross_sales":            "760.00",
  "client_refund_loss":     "0.00",
  "profit":                 "270.00"
}]}
```

### HTTP-коды

| Код | Когда |
|---|---|
| 200/201 | Успех |
| 401 | Не авторизован (нет/невалидный токен) |
| 403 | Авторизован, но нет нужного permission |
| 422 | Ошибка валидации, нарушение принадлежности (batch_item → batch, order_item → order) |
| 404 | Сущность не найдена |
| 409 | Бизнес-конфликт: `insufficient_stock`, `refund_exceeds_available`, `refund_exceeds_sold`, `*_has_relations` |
| 500 | Неожиданная ошибка |

---

## Queue Troubleshooting

Если `testapi-queue` не стартует с ошибкой:

`Composer detected issues in your platform ... require PHP >= 8.4.0`

проверьте версию PHP в контейнере:

```bash
docker compose exec app php -v
docker compose run --rm queue php -v
```

и пересоберите образы без кэша:

```bash
docker compose build --no-cache app queue
docker compose up -d app queue
docker compose logs -f queue
```

---

## Складской учёт и FIFO

Используется гибрид:

1. **`stock_movements`** — append-only журнал, source of truth.
2. **`storage_stocks`** — денормализованный текущий остаток на `(storage_id, product_id)`.
3. **`batch_items.available_qty`** — generated stored column, индексируется частичным индексом `WHERE available_qty > 0`.

### Алгоритм FIFO (в `OrderService`)

```
DB::transaction:
  для каждой строки products[]:
    batch_items WHERE product_id=? AND available_qty>0 ORDER BY id FOR UPDATE
    если SUM(available_qty) < qty:
        throw InsufficientStockException (409)
    идём по batch_items, списываем по min(remaining, available):
        create order_item_allocation
        batch_items.qty_sold += take
        stock_movement(type=sale, direction=-1)
        storage_stocks.qty -= take
```

Заказ 15 единиц при партиях `[5, 7, 20]` → три allocation: `5 + 7 + 3`.

### Защита от race condition

`SELECT … FOR UPDATE` на `batch_items` сериализует параллельные заказы по одному продукту. Без него два одновременных запроса могли бы продать товара больше, чем есть, — и тогда сработал бы CHECK-constraint (но это уже 500). Lock переводит конфликт в честный 409.

---

## Возвраты

### Возврат поставщику (`PurchaseRefundService`)

- `lockForUpdate` на `batch` и каждый `batch_item`.
- Проверка `qty <= available_qty` — нельзя вернуть проданный товар.
- `batch_items.qty_refunded_to_provider += qty`, `stock_movement(direction=-1)`, `storage_stocks -= qty`.
- Статус партии: `partially_refunded` или `refunded` после пересчёта.

### Возврат от клиента (`ClientRefundService`)

- `lockForUpdate` на `order` → `order_item` → allocations → `batch_item`.
- Возврат распределяется по `order_item_allocations` (FIFO по id).
- Каждая запись `client_refund_items` хранит `order_item_allocation_id` — это нужно для прибыли.
- `allocation.qty_returned += take`, `batch_items.qty_returned_by_clients += take`, `stock_movement(direction=+1)`, `storage_stocks += take`.
- Статус заказа: `partially_refunded` / `refunded`.

---

## Прибыль по партии

Считается через `order_item_allocations` — единственное место, где известно, какая партия покрыла какую продажу.

```
gross_sales              = SUM(allocations.qty * unit_sale_price)
client_refund_loss       = SUM(allocations.qty_returned * unit_sale_price)
effectively_sold         = SUM(allocations.qty - allocations.qty_returned)
purchase_cost_for_sold   = effectively_sold * batch_item.purchase_price

profit = gross_sales - client_refund_loss - purchase_cost_for_sold
```

Возврат поставщику в формулу **не входит**: он уменьшает остаток, но это разворот закупки, а не продажа. Видно отдельно через `qty_refunded_to_provider` на `batch_item`.

---

## Тесты

```bash
docker compose exec app php artisan test
```

Feature tests (Pest, RefreshDatabase на PostgreSQL):

**Бизнес-логика:**
- `PurchaseTest` — создание партии, инкремент склада, журнал движений.
- `OrderFifoTest` — split FIFO 5+7+3, 409 при нехватке, rollback при частичной нехватке.
- `PurchaseRefundTest` — списание со склада, отказ при попытке вернуть проданное.
- `ClientRefundTest` — restock + связь с allocation, отказ при превышении проданного.
- `ReportTest` — available products, скрытие распроданного, остатки на дату, прибыль с учётом клиентских возвратов.

**Auth + RBAC + CRUD:**
- `Auth/AuthTest` — register/login/logout/me, 401 без токена, валидация.
- `Permissions/PermissionTest` — admin имеет все права, manager/warehouse_manager/accountant имеют только свои, 401/403.
- `Users/UserCrudTest` — admin создаёт/обновляет/листает, назначает роли; без permission — 403.
- `Providers/ProviderCrudTest` — warehouse_manager создаёт, manager — нет; нельзя удалить со связями.
- `Clients/ClientCrudTest` — создание; нельзя удалить клиента с заказами.
- `Categories/CategoryCrudTest` — root/child, нельзя удалить с товарами.
- `Products/ProductCrudTest` — создание, фильтр по категории, нельзя удалить участвовавший в закупках.
- `Storages/StorageCrudTest` — создание, нельзя удалить склад с остатками.

---

## Структура проекта

```
app/
  Http/
    Controllers/   legacy + admin modular controllers
    Requests/      6 FormRequest
    Resources/     Batch, Order, PurchaseRefund, ClientRefund, BatchItem, OrderItem, etc.
  Services/        Purchase, Order (FIFO), PurchaseRefund, ClientRefund, Stock, AvailableProduct, BatchProfit
  Models/          domain models + File model
  DTO/             PurchasePayload, OrderPayload, PurchaseRefundPayload, ClientRefundPayload + Line-DTO
  Enums/           UserType, BatchStatus, OrderStatus, RefundStatus, StockMovementType
  Exceptions/      DomainException + ApiException + business exceptions
  Actions/         modular admin actions (Category, Provider, Product, File)
  Presenters/      ApiViewModel, ErrorViewModel, module view-models

database/
  migrations/      domain migrations + files table + user/provider link
  factories/       UserFactory (admin/manager/client states), Provider, Category, Product, Storage, Batch, BatchItem, Order
  seeders/         DemoSeeder — данные для FIFO split, заказов, refund-сценариев

docker/
  nginx/default.conf
  php/Dockerfile   PHP 8.4-fpm-alpine + pdo_pgsql, redis, opcache, bcmath, pcntl

routes/api.php
docker-compose.yml
```
