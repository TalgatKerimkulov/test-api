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
        Schema::create('purchase_refunds', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $t->string('code', 64)->unique();
            $t->string('status', 32)->default(RefundStatus::Completed->value);
            $t->text('reason')->nullable();
            $t->timestampTz('refunded_at');
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->timestamps();
        });

        $allowed = "'".implode("','", RefundStatus::values())."'";
        DB::statement("ALTER TABLE purchase_refunds ADD CONSTRAINT chk_pr_status CHECK (status IN ($allowed))");
        DB::statement('ALTER TABLE purchase_refunds ADD CONSTRAINT chk_pr_total_nonneg CHECK (total_amount >= 0)');

        Schema::create('purchase_refund_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('purchase_refund_id')->constrained('purchase_refunds')->cascadeOnDelete();
            $t->foreignId('batch_item_id')->constrained('batch_items')->restrictOnDelete();
            $t->integer('qty');
            $t->decimal('unit_purchase_price', 14, 2);
            $t->timestamps();

            $t->index('batch_item_id');
        });

        DB::statement('ALTER TABLE purchase_refund_items ADD CONSTRAINT chk_pri_qty CHECK (qty > 0)');
        DB::statement('ALTER TABLE purchase_refund_items ADD CONSTRAINT chk_pri_price_nonneg
            CHECK (unit_purchase_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_refund_items');
        Schema::dropIfExists('purchase_refunds');
    }
};
