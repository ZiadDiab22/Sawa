<?php

namespace App\Services\Admin;

use App\Repositories\DriverProfileRepository;
use App\Repositories\DriverDocumentRepository;
use Exception;

class DriverApprovalService
{
    public function __construct(
        protected DriverProfileRepository $driverRepository,
        protected DriverDocumentRepository $documentRepository
    ) {}

    public function getPendingDrivers()
    {
        return $this->driverRepository->getByStatus('pending');
    }

    public function getDriverDetails(int $driverId): array
    {
        $driver = $this->driverRepository->findByIdWithUser($driverId);
        $documents = $this->documentRepository->getByDriver($driverId);

        return [
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->user->name,
                'phone' => $driver->user->phone,
                'image' => $driver->user->profile_image
                    ? asset('storage/' . $driver->user->profile_image)
                    : null,
                'vehicle' => [
                    'model' => $driver->vehicle_model,
                    'color' => $driver->vehicle_color,
                    'plate' => $driver->vehicle_plate_number,
                ],
                'status' => $driver->status,
            ],
            'documents' => $documents,
        ];
    }

    public function approveDriver(int $driverId): void
    {
        $driver = $this->driverRepository->findById($driverId);

        if ($driver->status !== 'pending') {
            throw new Exception('Driver is not pending');
        }

        $this->driverRepository->updateStatus($driverId, 'approved');
    }

    public function rejectDriver(int $driverId): void
    {
        $driver = $this->driverRepository->findById($driverId);

        if ($driver->status !== 'pending') {
            throw new Exception('Driver is not pending');
        }

        $this->driverRepository->updateStatus($driverId, 'suspended');
    }


    public function getApprovedDrivers()
{
    $drivers = $this->driverRepository->getByStatus('approved');

    return $drivers->map(function ($driver) {
        return [
            'id' => $driver->id,
            'name' => $driver->user->name,
            'phone' => $driver->user->phone,
            'image' => $driver->user->profile_image
                ? asset('storage/' . $driver->user->profile_image)
                : null,

            'vehicle' => [
                'model' => $driver->vehicle_model,
                'color' => $driver->vehicle_color,
                'plate' => $driver->vehicle_plate_number,
            ],

            'city_id' => $driver->city_id,
            'vehicle_type_id' => $driver->vehicle_type_id,
            'status' => $driver->status,
        ];
    });
}

}
