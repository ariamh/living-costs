<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::create([
            'name' => 'Jakarta',
            'country' => 'Indonesia',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
