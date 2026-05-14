<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Agregamos los campos de tu diagrama aquí:
            $table->string('last_name', 45);
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->enum('state', ['active', 'inactive', 'banned'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Para tu deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};