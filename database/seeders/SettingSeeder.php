<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            [
                'key' => 'city_center_latitude',
                'value' => '33.564433',
            ],
            [
                'key' => 'city_center_longitude',
                'value' => '35.369105',
            ],
            [
                'key' => 'service_radius_km',
                'value' => '5.5',
            ],
            [
                'key' => 'company_commission_percentage',
                'value' => '20',
            ],
        ]);
    }
}
