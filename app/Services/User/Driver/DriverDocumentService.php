<?php
namespace App\Services\User\Driver;

use App\Models\DriverDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Repositories\DriverDocumentRepository;

class DriverDocumentService
{
    public function __construct(
        protected DriverDocumentRepository $repo
    ) {}

    private function uploadTwoFiles(array $files, string $folder): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($folder,'public');
            }
        }
        return $paths;
    }

    public function store(array $data): DriverDocument
    {
        $driverId = auth()->user()->driverProfile->id;

        $uploaded = $this->uploadTwoFiles($data['file_path'],'driver_docs/'.$data['type']);

        $doc = $this->repo->create([
            'driver_id' => $driverId,
            'type'      => $data['type'],
            'file_path'     => $uploaded,
            'expires_at'=> $data['expires_at'] ?? null,
            'status'    => 'pending'
        ]);

        return $doc;
    }

    public function update(int $id, array $data): DriverDocument
    {
        $doc = $this->repo->findOne($id);

        if (isset($data['file_path']) && is_array($data['file_path'])) {
            $uploaded = $this->uploadTwoFiles($data['file_path'],'driver_docs/'.$doc->type);
            $data['file_path'] = $uploaded;
        }
//update
        $data['status'] = 'pending';
        return $this->repo->update($id, $data);
    }


public function deleteFileFromDocument(int $id, string $file): DriverDocument
{
    $doc = $this->repo->findOne($id);

    $files = $doc->file_path ?? [];

    // تحقق أن الملف موجود بالمصفوفة
    if (! in_array($file, $files, true)) {
        abort(404, 'File not found in document');
    }

    // حذف من التخزين
    Storage::disk('public')->delete($file);

    // حذف من المصفوفة
    $doc->file_path = array_values(
        array_diff($files, [$file])
    );

    $doc->status = 'pending';
    $doc->save();
    return $doc;
}




public function updateSingleFile( int $id, string $oldFile,UploadedFile $newFile): DriverDocument
{

    $doc = $this->repo->findOne($id);

    $files = $doc->file_path ?? [];

    // تحقق أن الملف القديم موجود
    if (! in_array($oldFile, $files, true)) {
        abort(404, 'File not found in document');
    }

    // حذف القديم
    Storage::disk('public')->delete($oldFile);

    // رفع الجديد بنفس فولدر النوع
    $newPath = $newFile->store(
        'driver_docs/' . $doc->type,
        'public'
    );

    // استبدال المسار بالمصفوفة
    $files = array_map(
        fn($f) => $f === $oldFile ? $newPath : $f,
        $files
    );

    $doc->file_path = array_values($files);
    $doc->status = 'pending';
    $doc->save();

    return $doc;
}


    public function showAll(): array
    {
        $driverId = auth()->user()->driverProfile->id;
        $docs = $this->repo->findByDriverId($driverId);

        return array_map(fn($doc)=>[
            'id' => $doc->id,
            'type' => $doc->type,
            'file_path' => array_map(fn($f)=> asset('storage/'.$f), $doc->file_path),
            'expires_at' => $doc->expires_at,
            'status' => $doc->status,
            'created_at' => $doc->created_at,
        ], $docs);
    }


public function showAllGroupedByType(): array
{
    $driverId = auth()->user()->driverProfile->id;

    $docs = $this->repo->findByDriverId($driverId);

    // تهيئة المفاتيح دائماً
    $grouped = [
        'license'   => [],
        'driver_id' => [],
        'insurance' => [],
    ];

    foreach ($docs as $doc) {
        $grouped[$doc->type][] = [
            'id' => $doc->id,
            'file_path' => $doc->file_path
                ? array_map(fn($f) => asset('storage/' . $f), $doc->file_path)
                : [],
            'expires_at' => $doc->expires_at,
            'status' => $doc->status,
            'created_at' => $doc->created_at,
        ];
    }

    return $grouped;
}





//Admin
  public function approveDocument(int $id): DriverDocument
    {
        $doc = $this->repo->findOne($id);
        $doc->status = 'approved';
        $doc->save();

        return $doc;
    }

    public function rejectDocument(int $id): DriverDocument
    {
        $doc = $this->repo->findOne($id);
        $doc->status = 'rejected';
        $doc->save();

        return $doc;
    }

 public function getPendingDocuments(): array
    {
        $docs = \App\Models\DriverDocument::where('status', 'pending')
            ->with('driver.user', 'driver.vehicleType', 'driver.vehicleMake')
            ->get();

        return $docs->map(function($doc) {
            return [
                'document_id' => $doc->id,
                'type' => $doc->type,
                'file_path' => $doc->file_path ? array_map(fn($f) => asset('storage/'.$f), $doc->file_path) : [],
                'expires_at' => $doc->expires_at,
                'status' => $doc->status,
                'created_at' => $doc->created_at,
                'driver' => [
                    'id' => $doc->driver->id,
                    'user' => [
                        'id' => $doc->driver->user->id,
                        'name' => $doc->driver->user->name,
                        'email' => $doc->driver->user->email,
                        'phone' => $doc->driver->user->phone,
                    ],
                    'gender' => $doc->driver->gender,
                    'vehicle_type' => $doc->driver->vehicleType?->name,
                    'vehicle_make' => $doc->driver->vehicleMake?->name,
                    'vehicle_model' => $doc->driver->vehicle_model,
                    'vehicle_year' => $doc->driver->vehicle_year,
                    'vehicle_color' => $doc->driver->vehicle_color,
                    'vehicle_plate_number' => $doc->driver->vehicle_plate_number,
                    'status' => $doc->driver->status,
                    'is_status' => $doc->driver->is_status,
                ]
            ];
        })->toArray();
    }
}
