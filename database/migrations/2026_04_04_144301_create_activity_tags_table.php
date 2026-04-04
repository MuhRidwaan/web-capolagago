<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_tags', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('group_name', 50)
                  ->comment('Kelompok tag: audience | difficulty | facility | theme');
            $table->timestamp('created_at')->useCurrent();

            $table->index('group_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_tags');
    }
};