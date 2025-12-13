<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->insertGetId([
            'name'       => 'Admin',
            'email'      => 'admin@example.com',
            'phone'      => '123',
            'password'   => Hash::make('123123'),
            'gender'     => 'male'
        ]);

        DB::table('user_roles')->insert([
            'user_id'    => $adminId,
            'role_id'       => 4
        ]);
    }
}
