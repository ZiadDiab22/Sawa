<?php

namespace App\Http\Controllers\Api\Driver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
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


        'name'  => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|max:255|unique:users,email,' . auth()->id(),
        'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . auth()->id(),

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
//اضافة

public function deleteFile(string $field)
{
    $allowedFields = [
        'vehicle_document',
        'license_document',
        'insurance_document',
    ];

    if (! in_array($field, $allowedFields)) {
        return response()->json(['message' => 'Invalid field'], 422);
    }

    $profile = auth()->user()->driverProfile;

    if ($profile && $profile->$field) {
        Storage::disk('public')->delete($profile->$field);

        $profile->update([
            $field => null
        ]);
    }

    return response()->json([
        'message' => 'File deleted successfully'
    ]);
}


public function updateFile(Request $request, string $field)
{
    $allowedFields = [
        'vehicle_document',
        'license_document',
        'insurance_document',
    ];

    if (! in_array($field, $allowedFields)) {
        return response()->json(['message' => 'Invalid field'], 422);
    }

    $request->validate([
        'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096'
    ]);

    $profile = auth()->user()->driverProfile;

    // حذف الملف القديم
    if ($profile->$field) {
        Storage::disk('public')->delete($profile->$field);
    }

    // حفظ الجديد
    $path = $request->file('file')->store(
        str_replace('_document', 's', $field),
        'public'
    );

    $profile->update([
        $field => $path
    ]);

    return response()->json([
        'message' => 'File updated successfully',
        'file' => asset('storage/' . $path)
    ]);
}


public function deleteVehicleImage(Request $request)
{
    $request->validate([
        'image' => 'required|string'
    ]);

    $profile = auth()->user()->driverProfile;
    $images = $profile->vehicle_images ?? [];

    if (! in_array($request->image, $images)) {
        return response()->json(['message' => 'Image not found'], 404);
    }

    Storage::disk('public')->delete($request->image);

    $profile->update([
        'vehicle_images' => array_values(
            array_diff($images, [$request->image])
        )
    ]);

    return response()->json([
        'message' => 'Image deleted successfully'
    ]);
}

public function basicInfo()
{
    return response()->json(
        $this->driverProfileService->getBasicInfo(auth()->id())
    );
}

public function updateBasicInfo(Request $request)
{
    $data = $request->validate([
        'name'  => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|max:255|unique:users,email,' . auth()->id(),
        'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . auth()->id(),
        'gender' => 'sometimes|nullable|in:male,female',
        'profile_image' => 'sometimes|image|max:2048',
    ]);

    return response()->json([
        'message' => 'Profile updated successfully',
        'data' => $this->driverProfileService
            ->updateBasicInfo(auth()->id(), $data)
    ]);
}


public function showVehicle()
{
    return response()->json([
        'data' => $this->driverProfileService
            ->getVehicleInfo(auth()->id())
    ]);
}


}
