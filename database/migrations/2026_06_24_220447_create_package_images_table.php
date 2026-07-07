<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('package_images', function (Blueprint $table) {
            $table->id();
            // Relación con el paquete (Si se borra el paquete, se borran sus fotos de la BD)
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            // Relación opcional con el usuario que la subió
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('image_name', 100)->nullable();
            $table->string('url', 255);
            $table->timestamps(); // Esto ya te da la fecha de subida de forma automática
        });
    }

    public function down(): void {
        Schema::dropIfExists('package_images');
    }
};