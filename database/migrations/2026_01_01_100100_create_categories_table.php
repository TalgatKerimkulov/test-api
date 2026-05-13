<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('provider_id')->nullable()
                ->constrained('providers')->restrictOnDelete();
            $t->foreignId('parent_id')->nullable()
                ->constrained('categories')->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();

            $t->index('parent_id');
            $t->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
