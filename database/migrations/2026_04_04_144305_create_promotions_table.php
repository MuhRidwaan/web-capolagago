<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('promo_type_id');
            $table->string('name', 150);
            $table->string('code', 50)->unique()
                  ->comment('Kode voucher yang diinput tamu');
            $table->text('description')->nullable();
            $table->decimal('discount_value', 10, 2)
                  ->comment('Nilai diskon (persen atau nominal Rp)');
            $table->decimal('max_discount_amount', 12, 2)->nullable()
                  ->comment('Batas maksimum potongan untuk tipe persen');
            $table->decimal('min_order_amount', 12, 2)->default(0.00)
                  ->comment('Minimum total transaksi untuk pakai promo');
            $table->unsignedInteger('quota')->nullable()
                  ->comment('NULL = kuota tak terbatas');
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedTinyInteger('max_use_per_user')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->timestamps();

            $table->foreign('promo_type_id')
                  ->references('id')
                  ->on('promo_types')
                  ->restrictOnDelete();

            $table->index(['code', 'is_active']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};