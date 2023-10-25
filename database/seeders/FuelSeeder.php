<?php

namespace Database\Seeders;

use App\Models\Fuel;
use Illuminate\Database\Seeder;

class FuelSeeder extends Seeder
{
    public function run(): void
    {
        $fuels = [
            ['name' => 'Diesel'],
            ['name' => 'Gasoline'],
            ['name' => 'Petrol / Gas'],
            ['name' => 'Electricity'],
            ['name' => 'Hybrid'],
        ];

        foreach ($fuels as $fuel) {
            Fuel::create($fuel);
        }
    }
}
