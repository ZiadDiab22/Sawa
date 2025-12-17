<?php

namespace App\Services\User\Driver;

use App\Repositories\DriverRepository;
use Illuminate\Http\UploadedFile;

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
        $data['user_id'] = $userId;

        $data = $this->handleUploads($data);

        $profile = $this->DriverRepository->create($data);

        return $this->formatResponse($profile);
    }

    /* =========================
        UPDATE
    ========================== */
    public function updateProfile(int $userId, array $data): array
    {
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

    /* =========================
        RESPONSE FORMAT
    ========================== */
    private function formatResponse($profile): array
    {
        return [
            'id'    => $profile->id,
            'name'  => $profile->name,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'gender'=> $profile->gender,

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

            'vehicle_images' => collect($profile->vehicle_images)
                ->map(fn ($img) => asset('storage/'.$img)),

            'created_at' => $profile->created_at,
        ];
    }
}
