<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\DriverProfileService;
use Illuminate\Http\Request;

class DriverProfileController extends Controller
{
    public function __construct(
        protected DriverProfileService $driverProfileService
    ) {}

    // GET /api/driver/profile
    public function show()
    {
        return response()->json(
            $this->driverProfileService->getProfile(auth()->id())
        );
    }

    // PUT /api/driver/profile
    public function update(Request $request)
    {
        $request->validate([
            'city_id'             => 'required|exists:cities,id',
            'vehicle_type_id'     => 'required|exists:vehicle_types,id',
            'vehicle_model'       => 'required|string|max:255',
            'vehicle_color'       => 'required|string|max:255',
            'vehicle_plate_number'=> 'required|string|unique:driver_profiles,vehicle_plate_number,' . auth()->id() . ',user_id',
            'residence_location'  => 'nullable|string',
            'image'               => 'required|image|max:2048', // 👈 إجبارية
        ]);

        return response()->json([
            'message' => 'Driver profile updated successfully',
            'data' => $this->driverProfileService->updateProfile(
                auth()->id(),
                $request->all()
            )
        ]);
    }
}
