<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StorageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Storage extends Model
{
    /** @use HasFactory<StorageFactory> */
    use HasFactory;

    protected $fillable = ['name', 'address'];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(StorageStock::class);
    }
}
