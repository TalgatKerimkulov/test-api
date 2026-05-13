<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $t->string('sku', 64)->unique();
            $t->string('name');
            $t->decimal('default_sale_price', 14, 2)->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_price_nonneg
            CHECK (default_sale_price IS NULL OR default_sale_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
