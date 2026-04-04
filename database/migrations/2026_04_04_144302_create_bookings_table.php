<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique()
                  ->comment('Format: CAP-YYYYMMDD-XXXX');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('visit_date')->comment('Tanggal kunjungan utama');
            $table->date('checkout_date')->nullable()->comment('Untuk hitung LOS (glamping/homestay)');
            $table->unsignedSmallInteger('total_guests');
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('service_fee', 14, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->string('promo_code', 50)->nullable();
            $table->enum('status', [
                'pending',      // Baru dibuat, belum bayar
                'waiting_payment', // Menunggu konfirmasi pembayaran
                'confirmed',    // Pembayaran diterima
                'checked_in',   // Tamu sudah check-in
                'completed',    // Kunjungan selesai
                'cancelled',    // Dibatalkan
                'refunded',     // Dana dikembalikan
            ])->default('pending');
            $table->text('notes')->nullable()->comment('Catatan khusus dari tamu');
            $table->string('source', 50)->default('web')
                  ->comment('Asal pemesanan: web, mobile, whatsapp');
            $table->timestamps();

            $table->index('booking_code');
            $table->index(['user_id', 'status']);
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};