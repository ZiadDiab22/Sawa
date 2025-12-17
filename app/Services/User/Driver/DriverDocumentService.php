<?php

namespace App\Services\User\Driver;

use App\Models\DriverProfile;
use Exception;
use Illuminate\Http\UploadedFile;
use App\Repositories\DriverRepository;
use App\Repositories\DriverDocumentRepository;

class DriverDocumentService
{
    public function __construct(
        protected DriverDocumentRepository $repository,
        protected DriverProfile $driverProfileRepository
    ) {}

    public function list(int $userId)
    {
        $driver = $this->driverProfileRepository->getByUserId($userId);
        return $this->repository->getByDriver($driver->id);
    }

    public function upload(int $userId, array $data)
    {
        $driver = $this->driverProfileRepository->getByUserId($userId);

        $existing = $this->repository->findByType($driver->id, $data['type']);

        if ($existing && $existing->status === 'approved') {
            throw new Exception('Approved document cannot be updated');
        }

        $path = $data['file']->store('driver_documents', 'public');

        $payload = [
            'driver_id'  => $driver->id,
            'type'       => $data['type'],
            'file_path'  => $path,
            'expires_at' => $data['expires_at'] ?? null,
            'status'     => 'pending',
        ];

        if ($existing) {
            return $this->repository->update($existing, $payload);
        }

        return $this->repository->create($payload);
    }
}


