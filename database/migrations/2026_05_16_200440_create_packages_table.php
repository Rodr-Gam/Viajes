<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // 👤 Relación con usuarios
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); 

            // 🏙️ Relación con ciudades
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->onDelete('cascade');

            $table->string('duration'); 
            $table->date('departure_date'); 
            $table->integer('stock')->default(0); 
            
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_junior', 10, 2);
            $table->decimal('price_child', 10, 2);
            
            $table->string('image_path')->nullable(); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
            // ❌ ELIMINADO: $table->softDeletes(); para que ya no cree la columna 'deleted_at'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};