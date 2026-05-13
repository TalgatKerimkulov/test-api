<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'inn' => (string) fake()->unique()->numberBetween(1_000_000_000, 9_999_999_999),
            'email' => fake()->companyEmail(),
            'phone' => fake()->e164PhoneNumber(),
        ];
    }
}
