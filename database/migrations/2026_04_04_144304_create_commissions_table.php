<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('mitra_id')->constrained('mitra_profiles')->restrictOnDelete();
            $table->foreignId('booking_item_id')->constrained('booking_items')->restrictOnDelete();

            $table->decimal('gross_amount', 14, 2)
                  ->comment('Nilai transaksi item sebelum komisi');
            $table->decimal('commission_rate', 5, 2)
                  ->comment('Rate komisi yang berlaku saat transaksi (snapshot)');
            $table->decimal('commission_amount', 14, 2)
                  ->comment('Nominal komisi untuk platform');
            $table->decimal('net_amount', 14, 2)
                  ->comment('Yang diterima mitra = gross - commission');

            $table->enum('status', ['pending', 'processed', 'settled', 'cancelled'])
                  ->default('pending');
            $table->string('settlement_ref', 100)->nullable()
                  ->comment('Referensi transfer/disbursement ke mitra');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['mitra_id', 'status']);
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};