<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BatchProfitService
{
    /**
     * Per-batch profit, derived from order_item_allocations.
     *
     * profit = gross_sales
     *        - client_refund_loss
     *        - purchase_cost_for_effectively_sold_items
     *
     * Purchase refunds are not included (they reduce stock, not revenue).
     */
    public function calculate(
        ?string $from = null,
        ?string $to = null,
        ?int $batchId = null,
        ?int $providerId = null,
    ): Collection {
        $itemProfit = DB::table('batch_items as bi')
            ->leftJoin('order_item_allocations as oia', 'oia.batch_item_id', '=', 'bi.id')
            ->selectRaw('
                bi.batch_id,
                bi.id as batch_item_id,
                bi.purchase_price,
                COALESCE(SUM(oia.qty), 0)                                              AS sold_qty_gross,
                COALESCE(SUM(oia.qty_returned), 0)                                     AS returned_qty,
                COALESCE(SUM(oia.qty - oia.qty_returned), 0)                           AS effectively_sold,
                COALESCE(SUM(oia.qty * oia.unit_sale_price), 0)                        AS gross_sales,
                COALESCE(SUM(oia.qty_returned * oia.unit_sale_price), 0)               AS client_refund_loss
            ')
            ->groupBy('bi.id', 'bi.batch_id', 'bi.purchase_price');

        return DB::query()
            ->fromSub($itemProfit, 'ip')
            ->join('batches as b', 'b.id', '=', 'ip.batch_id')
            ->join('providers as pr', 'pr.id', '=', 'b.provider_id')
            ->selectRaw('
                b.id                                                                   AS batch_id,
                b.code,
                b.status,
                pr.name                                                                AS provider_name,
                b.purchased_at,
                SUM(ip.effectively_sold * ip.purchase_price)                           AS purchase_cost_for_sold,
                SUM(ip.gross_sales)                                                    AS gross_sales,
                SUM(ip.client_refund_loss)                                             AS client_refund_loss,
                SUM(ip.gross_sales)
                  - SUM(ip.client_refund_loss)
                  - SUM(ip.effectively_sold * ip.purchase_price)                       AS profit
            ')
            ->when($from, fn ($q, $v) => $q->where('b.purchased_at', '>=', $v))
            ->when($to, fn ($q, $v) => $q->where('b.purchased_at', '<=', $v))
            ->when($batchId, fn ($q, $v) => $q->where('b.id', $v))
            ->when($providerId, fn ($q, $v) => $q->where('b.provider_id', $v))
            ->groupBy('b.id', 'b.code', 'b.status', 'pr.name', 'b.purchased_at')
            ->orderByDesc('b.purchased_at')
            ->get();
    }
}
