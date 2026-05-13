<?php

use App\Enums\RefundStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_refunds', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $t->string('code', 64)->unique();
            $t->string('status', 32)->default(RefundStatus::Completed->value);
            $t->text('reason')->nullable();
            $t->timestampTz('refunded_at');
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->timestamps();
        });

        $allowed = "'".implode("','", RefundStatus::values())."'";
        DB::statement("ALTER TABLE client_refunds ADD CONSTRAINT chk_cr_status CHECK (status IN ($allowed))");
        DB::statement('ALTER TABLE client_refunds ADD CONSTRAINT chk_cr_total_nonneg CHECK (total_amount >= 0)');

        Schema::create('client_refund_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('client_refund_id')->constrained('client_refunds')->cascadeOnDelete();
            $t->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $t->foreignId('order_item_allocation_id')
                ->constrained('order_item_allocations')->restrictOnDelete();
            $t->integer('qty');
            $t->decimal('unit_sale_price', 14, 2);
            $t->timestamps();

            $t->index('order_item_id');
            $t->index('order_item_allocation_id');
        });

        DB::statement('ALTER TABLE client_refund_items ADD CONSTRAINT chk_cri_qty CHECK (qty > 0)');
        DB::statement('ALTER TABLE client_refund_items ADD CONSTRAINT chk_cri_price_nonneg
            CHECK (unit_sale_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('client_refund_items');
        Schema::dropIfExists('client_refunds');
    }
};
