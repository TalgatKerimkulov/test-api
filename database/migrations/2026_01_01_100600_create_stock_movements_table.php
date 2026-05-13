<?php

use App\Enums\StockMovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $t->foreignId('storage_id')->constrained('storages')->restrictOnDelete();
            $t->foreignId('batch_item_id')->nullable()
                ->constrained('batch_items')->restrictOnDelete();
            $t->string('type', 32);
            $t->smallInteger('direction');
            $t->integer('qty');
            $t->nullableMorphs('reference');
            $t->timestampTz('occurred_at');
            $t->timestamps();

            $t->index(['storage_id', 'occurred_at']);
            $t->index(['product_id', 'occurred_at']);
            $t->index('batch_item_id');
        });

        $allowed = "'".implode("','", StockMovementType::values())."'";
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT chk_sm_type CHECK (type IN ($allowed))");
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_sm_direction CHECK (direction IN (-1, 1))');
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_sm_qty_pos CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
