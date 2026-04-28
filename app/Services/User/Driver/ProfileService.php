<?php

namespace App\Services\User\Driver;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Repositories\DriverRepository;

class ProfileService
{
    public function __construct(
        protected DriverRepository $DriverRepository,
    ) {}

    /* =========================
        CREATE
    ========================== */
    public function createProfile(int $userId, array $data): array
{

    $user = User::findOrFail($userId);
    if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
        $path = $data['profile_image']->store('profiles', 'public');

        $user->update([
            'profile_image' => $path
        ]);
    }

    $data['user_id'] = $userId;
    $data['status'] = 'pending';
    $data['is_status'] = 'inactive';

    $data = $this->handleUploads($data);

    $profile = $this->DriverRepository->updateOrCreate($userId, $data);

    return $this->formatResponse($profile);
}


    /* =========================
        UPDATE
    ========================== */
    public function updateProfile(int $userId, array $data): array
    {
  $user = User::findOrFail($userId);

    $user->update([
        'name'  => $data['name']  ?? $user->name,
        'email' => $data['email'] ?? $user->email,
        'phone' => $data['phone'] ?? $user->phone,
        'gender' => $data['gender'] ?? $user->gender,

    ]);
      unset($data['status'], $data['is_status']);

        $data = $this->handleUploads($data);

        $profile = $this->DriverRepository
            ->updateByUserId($userId, $data);

        return $this->formatResponse($profile);
    }

    /* =========================
        SHOW
    ========================== */
    public function getProfile(int $userId): array
    {
        $profile = $this->DriverRepository->findByUserId($userId);
        return $this->formatResponse($profile);
    }

    /* =========================
        FILE HANDLING
    ========================== */
    private function handleUploads(array $data): array
    {
        foreach ([
            'vehicle_document'   => 'vehicle_documents',
            'license_document'   => 'licenses',
            'insurance_document' => 'insurances',
        ] as $field => $folder) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                $data[$field] = $data[$field]->store($folder, 'public');
            }
        }

        // صور المركبة (أكثر من صورة)
        if (isset($data['vehicle_images']) && is_array($data['vehicle_images'])) {
            $images = [];
            foreach ($data['vehicle_images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $images[] = $image->store('vehicles', 'public');
                }
            }
            $data['vehicle_images'] = $images;
        }

        return $data;
    }


  private function formatResponse($profile): array
{
    return [
        'id' => $profile->id,

        'name'  => $profile->user->name,
        'email' => $profile->user->email,
        'phone' => $profile->user->phone,
        'profile_image' => $profile->user->profile_image
        ? asset('storage/'.$profile->user->profile_image)
        : null,
        'gender' => $profile->gender,

        'vehicle_type' => $profile->vehicleType?->name,
        'vehicle_make' => $profile->vehicleMake?->name,

        'vehicle_model' => $profile->vehicle_model,
        'vehicle_year'  => $profile->vehicle_year,
        'vehicle_color' => $profile->vehicle_color,
        'vehicle_plate_number' => $profile->vehicle_plate_number,

        'vehicle_document' => $profile->vehicle_document
            ? asset('storage/'.$profile->vehicle_document)
            : null,

        'license_document' => $profile->license_document
            ? asset('storage/'.$profile->license_document)
            : null,

        'insurance_document' => $profile->insurance_document
            ? asset('storage/'.$profile->insurance_document)
            : null,

        'vehicle_images' => $profile->vehicle_images
            ? collect($profile->vehicle_images)->map(fn($img) => asset('storage/'.$img))
            : [],

        'status' => $profile->status,
        'is_status' => $profile->is_status,
        'created_at' => $profile->created_at,
    ];
}

public function getBasicInfo(int $userId): array
{
    $profile = $this->DriverRepository->findByUserId($userId);
    $user = $profile->user;

    return [
        'id' => $user->id,
        'name'   => $user->name,
        'email'  => $user->email,
        'phone'  => $user->phone,
        'gender' => $profile->gender,
        'profile_image' => $user->profile_image
            ? asset('storage/' . $user->profile_image)
            : null,
    ];
}



public function updateBasicInfo(int $userId, array $data): array
{
    $user = User::findOrFail($userId);
    $profile = $this->DriverRepository->findByUserId($userId);

    $user->update([
        'name'  => $data['name']  ?? $user->name,
        'email' => $data['email'] ?? $user->email,
        'phone' => $data['phone'] ?? $user->phone,
    ]);

    if (isset($data['gender'])) {
        $profile->update([
            'gender' => $data['gender']
        ]);
    }

    if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {

        if ($user->profile_image) {
            \Storage::disk('public')->delete($user->profile_image);
        }

        $path = $data['profile_image']->store('profiles', 'public');

        $user->update([
            'profile_image' => $path
        ]);
    }

    return [
        'name'   => $user->name,
        'email'  => $user->email,
        'phone'  => $user->phone,
        'gender' => $profile->gender,
        'profile_image' => $user->profile_image
            ? asset('storage/' . $user->profile_image)
            : null,
    ];
}



public function getVehicleInfo(int $userId): array
{
    $profile = $this->DriverRepository
        ->getVehicleInfoByUserId($userId);

    return [
        'vehicle_type' => $profile->vehicleType?->name,
        'vehicle_make' => $profile->vehicleMake?->name,

        'vehicle_model' => $profile->vehicle_model,
        'vehicle_year'  => $profile->vehicle_year,
        'vehicle_color' => $profile->vehicle_color,
        'vehicle_plate_number' => $profile->vehicle_plate_number,

        'vehicle_document' => $profile->vehicle_document
            ? asset('storage/' . $profile->vehicle_document)
            : null,

        'license_document' => $profile->license_document
            ? asset('storage/' . $profile->license_document)
            : null,

        'insurance_document' => $profile->insurance_document
            ? asset('storage/' . $profile->insurance_document)
            : null,

        'vehicle_images' => $profile->vehicle_images
            ? collect($profile->vehicle_images)->map(
                fn ($img) => asset('storage/' . $img)
            )
            : [],
    ];
}


 // إضافة
public function getDriverStatus(int $userId): array
{
    $profile = $this->DriverRepository
        ->getDriverWithDocumentsByUserId($userId);

    return [
        'driver_status' => [
            'status' => $profile->status,
            'is_status' => $profile->is_status,
            'can_receive_requests' => $profile->can_receive_requests,
        ],

        'documents' => $profile->documents->map(fn ($doc) => [
            'type' => $doc->type,
            'status' => $doc->status,
            'expires_at' => $doc->expires_at,
        ])->values(),
    ];
}


}
