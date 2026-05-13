<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->string('code', 64)->unique();
            $t->string('status', 32)->default(OrderStatus::Completed->value);
            $t->timestampTz('ordered_at');
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->timestamps();

            $t->index(['user_id', 'ordered_at']);
            $t->index('status');
        });

        $allowed = "'".implode("','", OrderStatus::values())."'";
        DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ($allowed))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_total_nonneg CHECK (total_amount >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
