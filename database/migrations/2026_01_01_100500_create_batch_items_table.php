<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $t->foreignId('storage_id')->constrained('storages')->restrictOnDelete();
            $t->integer('qty_purchased');
            $t->integer('qty_refunded_to_provider')->default(0);
            $t->integer('qty_sold')->default(0);
            $t->integer('qty_returned_by_clients')->default(0);
            $t->decimal('purchase_price', 14, 2);
            $t->decimal('sale_price', 14, 2);
            $t->timestamps();

            $t->index(['product_id', 'storage_id']);
            $t->index('batch_id');
        });

        DB::statement('ALTER TABLE batch_items
            ADD COLUMN available_qty integer
            GENERATED ALWAYS AS
              (qty_purchased - qty_refunded_to_provider - qty_sold + qty_returned_by_clients)
            STORED');

        DB::statement('ALTER TABLE batch_items ADD CONSTRAINT chk_bi_qty_purchased_pos
            CHECK (qty_purchased > 0)');
        DB::statement('ALTER TABLE batch_items ADD CONSTRAINT chk_bi_qty_nonneg
            CHECK (qty_refunded_to_provider >= 0
                AND qty_sold >= 0
                AND qty_returned_by_clients >= 0)');
        DB::statement('ALTER TABLE batch_items ADD CONSTRAINT chk_bi_consistent
            CHECK (qty_refunded_to_provider + qty_sold - qty_returned_by_clients <= qty_purchased)');
        DB::statement('ALTER TABLE batch_items ADD CONSTRAINT chk_bi_price_nonneg
            CHECK (purchase_price >= 0 AND sale_price >= 0)');

        DB::statement('CREATE INDEX idx_bi_available
            ON batch_items (product_id, storage_id, created_at)
            WHERE available_qty > 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_items');
    }
};
