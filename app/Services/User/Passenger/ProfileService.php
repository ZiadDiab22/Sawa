<?php

namespace App\Services\User\Passenger;

use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function getProfile(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $user->profile_image
                ? asset('storage/' . $user->profile_image)
                : null,
        ];
    }

    public function updateProfile(int $userId, array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['profile_image'] = $this->uploadImage($data['image']);
        }

        unset($data['image']);

        $user = $this->userRepository->update($userId, $data);

        return $this->getProfile($user->id);
    }

    private function uploadImage(UploadedFile $image): string
    {
        return $image->store('profiles', 'public');
    }
}
