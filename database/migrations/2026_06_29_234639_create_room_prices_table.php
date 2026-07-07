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
        Schema::create('room_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')
                ->constrained('hotels')
                ->onUpdate('cascade')
                ->onDelete('restrict'); 
            $table->enum('occupancy_type', [
                'single',
                'double',
                'triple',
                'quadruple',
                'suite',
            ]);
            $table->decimal('nightly_rate', 19, 4)->unsigned();
            $table->unsignedInteger('total_rooms')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['hotel_id', 'occupancy_type'], 'uq_hotel_occupancy');

            // Índice para búsquedas por precio
            $table->index('nightly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_prices');
    }
};
