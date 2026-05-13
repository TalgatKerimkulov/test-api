<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'ORD-'.Str::upper(Str::random(8)),
            'status' => OrderStatus::Completed->value,
            'ordered_at' => now(),
            'total_amount' => 0,
        ];
    }
}
