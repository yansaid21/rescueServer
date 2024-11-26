<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 100);
            $table->string('last_name', length: 100);
            $table->string('email', length: 255)->unique();
            $table->string('password', 100);
            $table->integer('id_card')->unique();
            $table->char('rhgb', length: 2)->nullable();
            $table->string('social_security', length: 100)->nullable();
            $table->char('phone_number', length: 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo_path')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('secondary_emails', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email', length: 255)->unique();
            $table->primary(['user_id', 'email']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
