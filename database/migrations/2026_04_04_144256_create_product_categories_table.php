<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('label', 100)->comment('Label tampilan di UI');
            $table->string('icon', 50)->nullable()->comment('Nama ikon heroicons/lucide');
            $table->string('color_hex', 7)->nullable()->comment('Warna badge kategori');
            $table->enum('type', ['internal', 'addon'])->default('internal')
                  ->comment('internal = produk Capolaga, addon = mitra lokal');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};