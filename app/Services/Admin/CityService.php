<?php
// app/Services/Admin/CityService.php
namespace App\Services\Admin;

use App\Repositories\CityRepository;

class CityService
{
    public function __construct(
        protected CityRepository $cityRepository
    ) {}

    public function createCity(array $data)
    {
        return $this->cityRepository->create($data);
    }

    public function updateCity(int $id, array $data)
    {
        return $this->cityRepository->update($id, $data);
    }

    public function deleteCity(int $id): void
    {
        $this->cityRepository->delete($id);
    }

    public function getCity(int $id)
    {
        return $this->cityRepository->findById($id);
    }
    public function getCities()
    {
        return $this->cityRepository->getAll();
    }
}
