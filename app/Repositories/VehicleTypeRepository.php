<?php
// app/Repositories/VehicleTypeRepository.php
namespace App\Repositories;

use App\Models\VehicleType;

class VehicleTypeRepository
{
    public function create(array $data): VehicleType
    {
        return VehicleType::create($data);
    }

    public function update(int $id, array $data): VehicleType
    {
        $vehicleType = VehicleType::findOrFail($id);
        $vehicleType->update($data);
        return $vehicleType;
    }

    public function delete(int $id): void
    {
        VehicleType::findOrFail($id)->delete();
    }

    public function findById(int $id): VehicleType
    {
        return VehicleType::findOrFail($id);
    }

    public function getAll()
    {
        return VehicleType::orderBy('name')->get();
    }
}
