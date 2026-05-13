<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DTO\OrderLine;
use App\DTO\OrderPayload;
use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Enums\UserType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $purchaseService = app(PurchaseService::class);
        $orderService = app(OrderService::class);

        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
        ]);

        $clientA = User::factory()->create([
            'type' => UserType::Client->value,
            'name' => 'Acme Buyer',
            'email' => 'client@example.test',
        ]);
        $clientB = User::factory()->create([
            'type' => UserType::Client->value,
            'name' => 'Other Buyer',
            'email' => 'other@example.test',
        ]);

        $main = Storage::factory()->create(['name' => 'Main warehouse']);
        $reserve = Storage::factory()->create(['name' => 'Reserve warehouse']);

        $providerA = Provider::factory()->create(['name' => 'Acme Electronics']);
        $providerB = Provider::factory()->create(['name' => 'OfficePro Supplies']);

        $electronics = Category::factory()->create([
            'provider_id' => $providerA->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);
        $peripherals = Category::factory()->create([
            'provider_id' => $providerA->id,
            'parent_id' => $electronics->id,
            'name' => 'Peripherals',
            'slug' => 'peripherals',
        ]);
        $office = Category::factory()->create([
            'provider_id' => $providerB->id,
            'name' => 'Office',
            'slug' => 'office',
        ]);
        $stationery = Category::factory()->create([
            'provider_id' => $providerB->id,
            'parent_id' => $office->id,
            'name' => 'Stationery',
            'slug' => 'stationery',
        ]);

        $mouse = Product::factory()->create([
            'category_id' => $peripherals->id, 'sku' => 'MS-100', 'name' => 'Mouse',
        ]);
        $keyboard = Product::factory()->create([
            'category_id' => $peripherals->id, 'sku' => 'KB-200', 'name' => 'Keyboard',
        ]);
        $pen = Product::factory()->create([
            'category_id' => $stationery->id, 'sku' => 'PN-001', 'name' => 'Pen',
        ]);
        $notebook = Product::factory()->create([
            'category_id' => $stationery->id, 'sku' => 'NB-001', 'name' => 'Notebook',
        ]);
        $stapler = Product::factory()->create([
            'category_id' => $stationery->id, 'sku' => 'ST-001', 'name' => 'Stapler',
        ]);

        // Three batches of "Mouse" to demonstrate FIFO split: 5, 7, 20
        $purchaseService->create(new PurchasePayload(
            providerId: $providerA->id,
            storageId: $main->id,
            purchasedAt: CarbonImmutable::now()->subDays(10),
            items: [
                new PurchaseLine($mouse->id, 5, '50.00', '80.00'),
                new PurchaseLine($keyboard->id, 10, '120.00', '180.00'),
            ],
        ));

        $purchaseService->create(new PurchasePayload(
            providerId: $providerA->id,
            storageId: $main->id,
            purchasedAt: CarbonImmutable::now()->subDays(7),
            items: [
                new PurchaseLine($mouse->id, 7, '52.00', '80.00'),
            ],
        ));

        $purchaseService->create(new PurchasePayload(
            providerId: $providerA->id,
            storageId: $main->id,
            purchasedAt: CarbonImmutable::now()->subDays(2),
            items: [
                new PurchaseLine($mouse->id, 20, '55.00', '85.00'),
            ],
        ));

        $purchaseService->create(new PurchasePayload(
            providerId: $providerB->id,
            storageId: $reserve->id,
            purchasedAt: CarbonImmutable::now()->subDays(5),
            items: [
                new PurchaseLine($pen->id, 100, '0.50', '1.50'),
                new PurchaseLine($notebook->id, 30, '3.00', '8.00'),
                new PurchaseLine($stapler->id, 15, '6.00', '12.00'),
            ],
        ));

        // Order: 15 mice — will split across 5+7+3 from three batches (FIFO)
        $orderService->create(new OrderPayload(
            userId: $clientA->id,
            orderedAt: CarbonImmutable::now()->subDay(),
            products: [
                new OrderLine($mouse->id, 15),
                new OrderLine($keyboard->id, 2),
            ],
        ));

        // Smaller second order from another client
        $orderService->create(new OrderPayload(
            userId: $clientB->id,
            orderedAt: CarbonImmutable::now(),
            products: [
                new OrderLine($notebook->id, 5),
                new OrderLine($pen->id, 20),
            ],
        ));

        $this->command?->info(sprintf(
            'Demo data ready. Admin: %s, Client: %s',
            $admin->email, $clientA->email,
        ));
    }
}
