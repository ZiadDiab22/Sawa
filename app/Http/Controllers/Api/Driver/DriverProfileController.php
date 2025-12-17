<?php

namespace App\Http\Controllers\Api\Driver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\Driver\ProfileService;
class DriverProfileController extends Controller
{
    public function __construct(
        protected ProfileService $driverProfileService
    ) {}


    // POST /api/driver/profile
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        return response()->json([
            'message' => 'Profile created successfully',
            'data' => $this->driverProfileService->createProfile(
                auth()->id(),
                $data
            )
        ], 201);
    }

    // PUT /api/driver/profile
    public function update(Request $request)
    {
        $data = $this->validateData($request, true);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $this->driverProfileService->updateProfile(
                auth()->id(),
                $data
            )
        ]);
    }

    // GET /api/driver/profile
    public function show()
    {
        return response()->json(
            $this->driverProfileService->getProfile(auth()->id())
        );
    }

    private function validateData(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',

            'gender' => 'nullable|in:male,female',

            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_make_id' => 'required|exists:vehicle_makes,id',

            'vehicle_model' => 'required|string|max:255',
            'vehicle_year'  => 'required|integer|min:1980|max:' . date('Y'),
            'vehicle_color' => 'required|string|max:255',

            'vehicle_plate_number' =>
                'required|string|unique:driver_profiles,vehicle_plate_number,' .
                auth()->id() . ',user_id',

            'vehicle_document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'license_document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'insurance_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',

            'vehicle_images'   => 'nullable|array',
            'vehicle_images.*' => 'image|max:2048',
        ]);
    }
}
