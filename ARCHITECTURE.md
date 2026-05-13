# Architecture Guide — test-api (Phase 2)

## Stack

- Laravel 13, PHP 8.4
- PostgreSQL
- Laravel Sanctum
- Spatie Laravel Permission

## Target Pattern

`Controller -> ActionData -> Action -> Presenter`

## Current Migration Status

- Legacy API remains active (`/api/v1/*` REST resources).
- New admin modular style is introduced for:
  - `category`: `/api/v1/admin/category/{create,index,show,update,delete,item-list}`
  - `provider`: `/api/v1/admin/provider/{create,index,show,update,delete,item-list}`
  - `product`: `/api/v1/admin/product/{create,index,show,update,delete,item-list}`
- Swagger UI: `/swagger`, OpenAPI spec: `/docs/openapi.yaml`.
- Auth split:
  - `POST /api/v1/auth/admin/login`
  - `POST /api/v1/auth/client/login`
  - `POST /api/v1/auth/login` (legacy compatibility)
- Client company flow:
  - `POST /api/v1/auth/client/company-create`
  - `users.provider_id` links client with provider-company.

## Rules Introduced

- `ActionData::fromRequest()` performs validation and throws `ApiException`.
- `Action::handle()` contains business logic only.
- `Presenter` returns unified envelope:
  - success: `{ "success": true, "result": ... }`
  - error: `{ "success": false, "error": {...}, "result": null }`

## Middleware

- `admin.scope` — only users with `type=admin`.
- `check.permission` — auto-permission name from `{controller}.{method}` with plural fallback.

## Modular Conventions

- One module per entity: `app/Actions/<Entity>/Admin/*`.
- One file per operation:
  - `<Entity>CreateActionData`, `<Entity>CreateAction`
  - `<Entity>IndexActionData`, `<Entity>IndexAction`
  - `<Entity>ShowActionData`, `<Entity>ShowAction`
  - `<Entity>UpdateActionData`, `<Entity>UpdateAction`
  - `<Entity>DeleteActionData`, `<Entity>DeleteAction`
  - `<Entity>ItemListActionData`, `<Entity>ItemListAction`
- Controllers are separated by scope in `app/Http/Controllers/Admin/*Controller.php`.
- Responses in modular endpoints use presenter envelope:
  - success: `{ "success": true, "result": ... }`
  - error: `{ "success": false, "error": {...}, "result": null }`

## Next Phases

1. Move remaining modules to `ActionData/Action/Presenter`.
2. Introduce separated `seller` and `buyer` route segments if required by product scope.
3. Replace legacy JSON resource responses with unified presenters.
4. Add OpenAPI coverage for remaining endpoints.
