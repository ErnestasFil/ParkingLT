<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    protected $seeder = [
        FuelSeeder::class,
        RoleSeeder::class,
        StatusSeeder::class,
    ];
    public function run()
    {
        $this->call($this->seeder);
    }
}
