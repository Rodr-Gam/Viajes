<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla: hotels
     * Relación: tiene muchos room_prices (1:N)
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            // Primary key
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');

            $table->string('name', 50);
            $table->string('destination', 50)->nullable();
            $table->string('hgdl_key', 20)->nullable()->index();
            $table->string('supplier', 45)->nullable();
            $table->string('booking_source', 50)->nullable();
            $table->decimal('provider_cost', 19, 4)->nullable()->unsigned();
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();


            $table->index('name');
            $table->index('destination');
            $table->index('booking_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
