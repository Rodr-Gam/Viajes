<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Relación con la tabla ciudades (Se mantiene intacta)
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->onDelete('cascade'); 

            $table->string('address');
            $table->integer('stars')->default(3);
            $table->decimal('price_per_night', 10, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // ✨ NUEVO CAMPO AGREGADO (VARCHAR de 20 caracteres como tu diagrama)
            $table->string('hgdl_key', 20)->nullable(); 
            // ❌ ELIMINADO: $table->string('image_path')
            
            // Nuevos campos anteriores (Se mantienen intactos)
            $table->string('name_supplier')->nullable();
            $table->string('booking_source')->nullable();
            $table->decimal('provider_cost', 10, 2)->nullable();
            $table->text('observations')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};