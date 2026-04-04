<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('slot_id')->nullable()->constrained('product_slots')->nullOnDelete();
            $table->boolean('is_addon')->default(false)
                  ->comment('TRUE = item ini adalah add-on activity mitra');

            // Snapshot harga saat booking (penting! harga produk bisa berubah)
            $table->string('product_name_snapshot', 200);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 12, 2)
                  ->comment('Harga per unit saat booking');
            $table->decimal('subtotal', 14, 2)
                  ->comment('quantity × unit_price');

            $table->timestamps();

            $table->index(['booking_id', 'is_addon']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};