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
    Schema::create('packages', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        
        // Relación con ciudades (Destino del paquete)
        $table->foreignId('city_id')
              ->constrained('cities')
              ->onDelete('cascade');

        $table->string('duration'); // Ej: "5 Días / 4 Noches"
        $table->date('departure_date'); // Fecha fija de salida
        $table->integer('stock')->default(0); // Cupos disponibles
        
        // Columnas de precios independientes por tipo de pasajero
        $table->decimal('price_adult', 10, 2);
        $table->decimal('price_junior', 10, 2);
        $table->decimal('price_child', 10, 2);
        
        $table->string('image_path')->nullable(); // Foto del paquete
        $table->enum('status', ['active', 'inactive'])->default('active');
        
        $table->timestamps();
        $table->softDeletes(); // Por si el admin quiere "borrarlo" sin romper el historial
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
