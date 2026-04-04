<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('business_name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00)
                  ->comment('Persentase komisi platform (%)');
            $table->enum('subscription_type', ['free', 'basic', 'premium'])->default('free');
            $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])->default('pending');
            $table->date('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_profiles');
    }
};