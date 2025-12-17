<?php
// app/Repositories/CityRepository.php
namespace App\Repositories;

use App\Models\City;

class CityRepository
{
    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(int $id, array $data): City
    {
        $city = City::findOrFail($id);
        $city->update($data);
        return $city;
    }

    public function delete(int $id): void
    {
        $city = City::findOrFail($id);
        $city->delete();
    }

    public function findById(int $id): City
    {
        return City::findOrFail($id);
    }

    public function getAll()
    {
        return City::orderBy('name')->get();
    }
}
