<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('label', 150)->comment('Label tampilan di UI');
            $table->string('group', 50)->default('midtrans')->comment('midtrans | general');
            $table->boolean('is_secret')->default(false)->comment('Tampilkan sebagai password field');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
