<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{

    public function run(): void
    {
        $allStatus = [
            ['name' => 'Formed'],
            ['name' => 'Canceled'],
            ['name' => 'Paid'],
            ['name' => 'Unpaid'],
        ];

        foreach ($allStatus as $status) {
            Status::create($status);
        }
    }
}
