<?php

use App\Enums\BatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $t->foreignId('storage_id')->constrained('storages')->restrictOnDelete();
            $t->string('code', 64)->unique();
            $t->string('status', 32)->default(BatchStatus::Completed->value);
            $t->timestampTz('purchased_at');
            $t->decimal('total_cost', 14, 2)->default(0);
            $t->timestamps();

            $t->index(['provider_id', 'purchased_at']);
            $t->index('purchased_at');
            $t->index('status');
        });

        $allowed = "'".implode("','", BatchStatus::values())."'";
        DB::statement("ALTER TABLE batches ADD CONSTRAINT chk_batches_status CHECK (status IN ($allowed))");
        DB::statement('ALTER TABLE batches ADD CONSTRAINT chk_batches_cost_nonneg CHECK (total_cost >= 0)');

        DB::statement("CREATE INDEX idx_batches_active ON batches(provider_id, purchased_at)
            WHERE status IN ('completed','partially_refunded')");
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
