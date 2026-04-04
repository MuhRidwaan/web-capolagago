<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->date('slot_date');
            $table->time('start_time')->nullable()->comment('Jam mulai, null = bebas');
            $table->unsignedSmallInteger('total_slots')->comment('Total unit/kapasitas tersedia');
            $table->unsignedSmallInteger('booked_slots')->default(0)->comment('Sudah dipesan');
            $table->boolean('is_blocked')->default(false)->comment('Tutup manual oleh admin/mitra');
            $table->timestamps();

            // Satu produk hanya punya satu slot per tanggal + jam
            $table->unique(['product_id', 'slot_date', 'start_time']);
            $table->index(['product_id', 'slot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_slots');
    }
};