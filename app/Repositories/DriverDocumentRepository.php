<?php

namespace App\Repositories;

use App\Models\DriverDocument;

class DriverDocumentRepository
{
    public function create(array $data): DriverDocument
    {
        return DriverDocument::create($data);
    }

    public function update(int $id, array $data): DriverDocument
    {
        $doc = DriverDocument::findOrFail($id);
        $doc->update($data);
        return $doc;
    }

    public function findByDriverId(int $driverId): array
    {
        return DriverDocument::where('driver_id',$driverId)->get()->all();
    }

    public function findOne(int $id): DriverDocument
    {
        return DriverDocument::findOrFail($id);
    }
}
