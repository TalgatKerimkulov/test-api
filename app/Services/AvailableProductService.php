<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AvailableProductService
{
    /**
     * Returns rows with: id, name, category_name, price, qty.
     *
     * "price" is the sale_price of the oldest still-available batch_item.
     */
    public function list(): Collection
    {
        return DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->whereNull('p.deleted_at')
            ->selectRaw('p.id, p.name, c.name as category_name')
            ->selectSub(
                DB::table('batch_items')
                    ->whereColumn('batch_items.product_id', 'p.id')
                    ->where('available_qty', '>', 0)
                    ->orderBy('id')
                    ->select('sale_price')
                    ->limit(1),
                'price'
            )
            ->selectSub(
                DB::table('batch_items')
                    ->whereColumn('batch_items.product_id', 'p.id')
                    ->selectRaw('COALESCE(SUM(available_qty), 0)'),
                'qty'
            )
            ->whereExists(fn ($q) => $q->from('batch_items')
                ->whereColumn('batch_items.product_id', 'p.id')
                ->where('batch_items.available_qty', '>', 0))
            ->orderBy('p.name')
            ->get();
    }
}
