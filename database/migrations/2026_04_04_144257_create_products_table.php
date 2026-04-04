<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->nullable()
                  ->constrained('mitra_profiles')->nullOnDelete()
                  ->comment('NULL = produk milik Capolaga sendiri');
            $table->unsignedTinyInteger('category_id');
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->string('short_desc', 300)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('price_label', 50)->default('/malam')
                  ->comment('Satuan: /malam, /orang, /sesi, /unit');
            $table->unsignedTinyInteger('min_pax')->default(1);
            $table->unsignedSmallInteger('max_pax')->default(10);
            $table->unsignedSmallInteger('max_capacity')->default(1)
                  ->comment('Jumlah unit/slot tersedia per hari');
            $table->decimal('duration_hours', 4, 1)->nullable()
                  ->comment('Durasi aktivitas dalam jam (untuk add-on)');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_desc', 300)->nullable();
            $table->timestamps();

            $table->foreign('category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->restrictOnDelete();

            $table->index('category_id');
            $table->index('mitra_id');
            $table->index(['is_active', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};