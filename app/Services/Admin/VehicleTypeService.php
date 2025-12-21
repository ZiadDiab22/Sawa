<?php
namespace App\Services\Admin;

use App\Repositories\VehicleTypeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VehicleTypeService
{
    public function __construct(
        protected VehicleTypeRepository $vehicleTypeRepository
    ) {}

    public function createVehicleType(array $data)
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('vehicle_types', 'public');
        }

        return $this->vehicleTypeRepository->create($data);
    }

    public function updateVehicleType(int $id, array $data)
    {
        $vehicleType = $this->vehicleTypeRepository->findById($id);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // حذف الصورة القديمة
            if ($vehicleType->image) {
                Storage::disk('public')->delete($vehicleType->image);
            }

            $data['image'] = $data['image']->store('vehicle_types', 'public');
        }

        return $this->vehicleTypeRepository->update($id, $data);
    }

    public function deleteVehicleType(int $id): void
    {
        $vehicleType = $this->vehicleTypeRepository->findById($id);

        if ($vehicleType->image) {
            Storage::disk('public')->delete($vehicleType->image);
        }

        $this->vehicleTypeRepository->delete($id);
    }

    public function getVehicleType(int $id)
    {
        return $this->vehicleTypeRepository->findById($id);
    }

    public function getVehicleTypes()
    {
        return $this->vehicleTypeRepository->getAll()->map(function ($type) {
            return $this->format($type);
        });
    }

    private function format($vehicleType): array
    {
        return [
            'id' => $vehicleType->id,
            'name' => $vehicleType->name,
            'image' => $vehicleType->image
                ? asset('storage/' . $vehicleType->image)
                : null,
            'base_fare' => $vehicleType->base_fare,
            'per_km' => $vehicleType->per_km,
            'per_minute' => $vehicleType->per_minute,
            'minimum_fare' => $vehicleType->minimum_fare,
            'is_active' => $vehicleType->is_active,
        ];
    }
}
