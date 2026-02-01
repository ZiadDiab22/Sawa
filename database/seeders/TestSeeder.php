<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User, UserRole, DriverProfile,
    RideRequest, Ride, RideRequestResponse,
    CompanyCommission, DriverProfit };

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'ziad',
                'email' => 'zd@gmail.com',
                'phone' => '111',
            ],
            [
                'name' => 'youssef',
                'email' => 'ys@gmail.com',
                'phone' => '333',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        UserRole::insert([
            ['user_id' => 2, 'role_id' => 1],
            ['user_id' => 3, 'role_id' => 1],
            ['user_id' => 3, 'role_id' => 2],
        ]);

        DriverProfile::create([
            'user_id' => 3,
            'vehicle_model' => 'Toyota Corolla',
            'vehicle_year' => 2020,
            'vehicle_color' => 'White',
            'vehicle_plate_number' => 'ABC-123',
            'wallet' => 4929680,
        ]);

        $rideData = [
            'user_id' => 2,
            'pickup_lat' => 33.563805,
            'pickup_lng' => 35.370368,
            'drop_lat' => 33.5626087,
            'drop_lng' => 36.3690464,
            'distance_km' => 92.53,
            'price' => 6237100,
            'duration_minutes' => 278,
            'vehicle_type_id' => 1,
            'passengers' => 3,
        ];

        $statuses = [
            'accepted','accepted','accepted','accepted',
            'pending','cancelled','accepted'
        ];

        foreach ($statuses as $status) {
            RideRequest::create($rideData + ['status' => $status]);
        }

        $rideData = [
            'user_id' => 2,
            'start_lat' => 33.563805,
            'start_lng' => 35.370368,
            'end_lat' => 33.5626087,
            'end_lng' => 36.3690464,
            'distance_km' => 92.53,
            'price' => 9237100,
            'passengers' => 3,
            'duration_minutes' => 278,
        ];

        $rides = [
            [4, 'completed', 6211],
            [3, 'cancelled', 7541],
            [2, 'cancelled', 1605],
            [1, 'driver_on_way', 1516],
            [7, 'on_going', 2030],
        ];

        foreach ($rides as [$requestId, $status, $code]) {
            Ride::create($rideData + [
                    'ride_request_id' => $requestId,
                    'driver_id' => 3,
                    'status' => $status,
                    'code' => $code,
                ]);
        }

        RideRequestResponse::insert([
            ['ride_request_id' => 5, 'driver_id' => 3, 'status' => 'skipped'],
            ['ride_request_id' => 4, 'driver_id' => 3, 'status' => 'accepted'],
            ['ride_request_id' => 3, 'driver_id' => 3, 'status' => 'accepted'],
            ['ride_request_id' => 2, 'driver_id' => 3, 'status' => 'accepted'],
            ['ride_request_id' => 1, 'driver_id' => 3, 'status' => 'accepted'],
            ['ride_request_id' => 7, 'driver_id' => 3, 'status' => 'accepted'],
        ]);

        CompanyCommission::insert([
            ['user_id' => 3, 'ride_id' => 1, 'amount' => 1247420],
        ]);

        DriverProfit::insert([
            ['user_id' => 3, 'ride_id' => 1, 'amount' => 4989680],
            ['user_id' => 3, 'ride_id' => 2, 'amount' => -60000],
        ]);

    }
}
