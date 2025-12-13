<?php

namespace App\Repositories;

use App\Models\DriverDocument;

class DriverDocumentRepository
{
    public function getByDriver(int $driverId)
    {
        return DriverDocument::where('driver_id', $driverId)->get();
    }

    public function findByType(int $driverId, string $type): ?DriverDocument
    {
        return DriverDocument::where([
            'driver_id' => $driverId,
            'type' => $type,
        ])->first();
    }

    public function create(array $data): DriverDocument
    {
        return DriverDocument::create($data);
    }

    public function update(DriverDocument $document, array $data): DriverDocument
    {
        $document->update($data);
        return $document;
    }
}

