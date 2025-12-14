<?php

namespace App\Services\User\Driver;

use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;

class DriverProfileService
{
    public function __construct(
        protected DriverRepository $DriverRepository,
        protected UserRepository $userRepository
    ) {}

    public function getProfile(int $userId): array
    {
        $profile = $this->DriverRepository->getByUserId($userId);

        return [
            'name'   => $profile->user->name,
            'email'  => $profile->user->email,
            'phone'  => $profile->user->phone,
            'image'  => $profile->user->profile_image
                ? asset('storage/' . $profile->user->profile_image)
                : null,

            'city_id'         => $profile->city_id,
            'vehicle_type_id' => $profile->vehicle_type_id,
            'vehicle_model'   => $profile->vehicle_model,
            'vehicle_color'   => $profile->vehicle_color,
            'plate_number'    => $profile->vehicle_plate_number,
            'status'          => $profile->status,
        ];
    }

    public function updateProfile(int $userId, array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $path = $data['image']->store('drivers', 'public');
            $this->userRepository->update($userId, [
                'profile_image' => $path
            ]);
        }

        unset($data['image']);

        $profile = $this->DriverRepository->updateOrCreate(
            $userId,
            $data
        );

        return $this->getProfile($userId);
    }
}
