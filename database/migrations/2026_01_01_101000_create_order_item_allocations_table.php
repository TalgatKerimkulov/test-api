<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_allocations', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $t->foreignId('batch_item_id')->constrained('batch_items')->restrictOnDelete();
            $t->integer('qty');
            $t->integer('qty_returned')->default(0);
            $t->decimal('unit_purchase_price', 14, 2);
            $t->decimal('unit_sale_price', 14, 2);
            $t->timestamps();

            $t->index('order_item_id');
            $t->index('batch_item_id');
        });

        DB::statement('ALTER TABLE order_item_allocations ADD CONSTRAINT chk_oia_qty
            CHECK (qty > 0 AND qty_returned >= 0 AND qty_returned <= qty)');
        DB::statement('ALTER TABLE order_item_allocations ADD CONSTRAINT chk_oia_price_nonneg
            CHECK (unit_purchase_price >= 0 AND unit_sale_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_allocations');
    }
};
