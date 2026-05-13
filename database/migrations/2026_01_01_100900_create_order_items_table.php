<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $t->integer('qty');
            $t->integer('qty_refunded')->default(0);
            $t->decimal('unit_price', 14, 2);
            $t->timestamps();

            $t->index('order_id');
            $t->index('product_id');
        });

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_oi_qty_pos CHECK (qty > 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_oi_qty_refunded
            CHECK (qty_refunded >= 0 AND qty_refunded <= qty)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_oi_price_nonneg
            CHECK (unit_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
