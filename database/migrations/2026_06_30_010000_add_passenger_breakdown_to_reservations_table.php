<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedSmallInteger('adults')->default(0)->after('reserved_seats');
            $table->unsignedSmallInteger('juniors')->default(0)->after('adults');
            $table->unsignedSmallInteger('children')->default(0)->after('juniors');
            $table->decimal('unit_price_adult', 10, 2)->nullable()->after('children');
            $table->decimal('unit_price_junior', 10, 2)->nullable()->after('unit_price_adult');
            $table->decimal('unit_price_child', 10, 2)->nullable()->after('unit_price_junior');
            $table->decimal('total_amount', 12, 2)->default(0)->after('unit_price_child');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'adults',
                'juniors',
                'children',
                'unit_price_adult',
                'unit_price_junior',
                'unit_price_child',
                'total_amount',
            ]);
        });
    }
};
