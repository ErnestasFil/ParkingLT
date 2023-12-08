<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Unconfirmed'],
            ['name' => 'User'],
            ['name' => 'Administrator']
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
