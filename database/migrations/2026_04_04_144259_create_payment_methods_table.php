<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->enum('provider', ['midtrans', 'xendit', 'manual'])->default('midtrans');
            $table->enum('type', ['va', 'ewallet', 'qris', 'cc', 'cstore', 'manual']);
            $table->string('logo_path', 255)->nullable();
            $table->decimal('fee_flat', 10, 2)->default(0.00)
                  ->comment('Biaya tetap per transaksi (Rp)');
            $table->decimal('fee_percent', 5, 3)->default(0.000)
                  ->comment('Biaya persentase per transaksi (%)');
            $table->decimal('min_amount', 12, 2)->default(10000.00);
            $table->decimal('max_amount', 12, 2)->nullable()
                  ->comment('NULL = tidak ada batas maksimum');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};