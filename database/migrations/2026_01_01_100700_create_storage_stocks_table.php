<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_stocks', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('storage_id')->constrained('storages')->restrictOnDelete();
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $t->integer('qty')->default(0);
            $t->timestamps();
            $t->unique(['storage_id', 'product_id']);
        });

        DB::statement('ALTER TABLE storage_stocks ADD CONSTRAINT chk_ss_qty_nonneg CHECK (qty >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_stocks');
    }
};
