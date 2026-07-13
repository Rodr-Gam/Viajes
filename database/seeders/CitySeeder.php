<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Guadalajara', 'country' => 'México'],
            ['name' => 'Ciudad de México', 'country' => 'México'],
            ['name' => 'Cancún', 'country' => 'México'],
            ['name' => 'Monterrey', 'country' => 'México'],
            ['name' => 'Nueva York', 'country' => 'Estados Unidos'],
            ['name' => 'Los Ángeles', 'country' => 'Estados Unidos'],
            ['name' => 'Madrid', 'country' => 'España'],
            ['name' => 'Barcelona', 'country' => 'España'],
            ['name' => 'París', 'country' => 'Francia'],
            ['name' => 'Roma', 'country' => 'Italia'],
            ['name' => 'Tokio', 'country' => 'Japón'],
            ['name' => 'Londres', 'country' => 'Reino Unido'],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate($city);
        }
    }
}
