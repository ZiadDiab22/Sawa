<?php
// app/Services/Admin/VehicleTypeService.php
namespace App\Services\Admin;

use App\Repositories\VehicleTypeRepository;

class VehicleTypeService
{
    public function __construct(
        protected VehicleTypeRepository $vehicleTypeRepository
    ) {}

    public function createVehicleType(array $data)
    {
        return $this->vehicleTypeRepository->create($data);
    }

    public function updateVehicleType(int $id, array $data)
    {
        return $this->vehicleTypeRepository->update($id, $data);
    }

    public function deleteVehicleType(int $id): void
    {
        $this->vehicleTypeRepository->delete($id);
    }

    public function getVehicleType(int $id)
    {
        return $this->vehicleTypeRepository->findById($id);
    }

    public function getVehicleTypes()
    {
        return $this->vehicleTypeRepository->getAll();
    }
}
