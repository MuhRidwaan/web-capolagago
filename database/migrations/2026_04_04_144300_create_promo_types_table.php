<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->string('description', 300)->nullable();
            $table->enum('discount_type', ['percent', 'fixed', 'free_addon', 'early_bird', 'bundle']);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_types');
    }
};