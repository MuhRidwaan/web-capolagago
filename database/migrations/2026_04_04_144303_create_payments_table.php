<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->string('payment_code', 50)->unique()
                  ->comment('Internal payment reference code');
            $table->unsignedTinyInteger('payment_method_id');
            $table->decimal('amount', 14, 2);
            $table->decimal('fee_amount', 10, 2)->default(0.00)
                  ->comment('Biaya gateway yang dikenakan');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])
                  ->default('pending');

            // Data dari gateway
            $table->string('gateway_transaction_id', 255)->nullable()
                  ->comment('ID transaksi dari Midtrans/Xendit');
            $table->string('gateway_order_id', 255)->nullable();
            $table->string('va_number', 50)->nullable()
                  ->comment('Nomor Virtual Account jika metode VA');
            $table->string('qr_url', 500)->nullable()
                  ->comment('URL QR Code jika metode QRIS');
            $table->json('gateway_response')->nullable()
                  ->comment('Raw response dari payment gateway');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->foreign('payment_method_id')
                  ->references('id')
                  ->on('payment_methods')
                  ->restrictOnDelete();

            $table->index('booking_id');
            $table->index('status');
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};