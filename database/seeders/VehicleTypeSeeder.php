<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Car',
            'VIP',
            'Bike',
            'Van'
        ];

        foreach ($types as $index => $type) {
            VehicleType::firstOrCreate(
                ['name' => $type],
                ['is_active' => $index === 0]
            );
        }
    }
}
