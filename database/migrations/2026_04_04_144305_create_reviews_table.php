<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedTinyInteger('rating')
                  ->comment('Nilai 1–5');
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            // Satu user hanya bisa review satu produk per booking
            $table->unique(['user_id', 'booking_id', 'product_id']);
            $table->index(['product_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};