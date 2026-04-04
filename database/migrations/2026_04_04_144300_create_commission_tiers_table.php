<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_tiers', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 100);
            $table->decimal('min_monthly_revenue', 14, 2)->default(0.00)
                  ->comment('Minimum omzet bulanan mitra (Rp) untuk masuk tier ini');
            $table->decimal('commission_rate', 5, 2)
                  ->comment('Tarif komisi platform (%)');
            $table->decimal('subscription_discount', 5, 2)->default(0.00)
                  ->comment('Diskon biaya subscription (%)');
            $table->string('description', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_tiers');
    }
};