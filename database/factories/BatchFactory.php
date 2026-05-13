<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Provider;
use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'storage_id' => Storage::factory(),
            'code' => 'BATCH-'.Str::upper(Str::random(8)),
            'status' => BatchStatus::Completed->value,
            'purchased_at' => now(),
            'total_cost' => 0,
        ];
    }
}
