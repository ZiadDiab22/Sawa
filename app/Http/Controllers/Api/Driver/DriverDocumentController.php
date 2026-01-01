<?php
namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\Driver\DriverDocumentService;

class DriverDocumentController extends Controller
{
    public function __construct(
        protected DriverDocumentService $driverDocumentService
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:license,driver_id,insurance',
            'file_path' => 'required|array|size:2',
            'file_path.*' => 'file|mimes:jpg,jpeg,png,pdf|max:4096',
            'expires_at' => 'nullable|date'
        ]);

        $doc = $this->driverDocumentService->store($data);

        return response()->json([
            'message'=>'Document uploaded successfully',
            'data'=>$doc
        ],201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'file_path' => 'sometimes|array|size:2',
            'file_path.*' => 'file|mimes:jpg,jpeg,png,pdf|max:4096',
            'expires_at'=>'nullable|date',
            'status'=>'sometimes|in:pending,approved,rejected'
        ]);

        $doc = $this->driverDocumentService->update($id,$data);

        return response()->json([
            'message'=>'Document updated successfully',
            'data'=>$doc
        ]);
    }

    public function show()
    {
        return response()->json(
            $this->driverDocumentService->showAll()
        );
    }

    //Admin

      public function approve(int $id)
    {
        $doc = $this->driverDocumentService->approveDocument($id);

        return response()->json([
            'message' => 'Document approved successfully',
            'data' => $doc
        ]);
    }
    public function reject(int $id)
    {
        $doc = $this->driverDocumentService->rejectDocument($id);

        return response()->json([
            'message' => 'Document rejected successfully',
            'data' => $doc
        ]);
    }

     public function pendingDocuments()
    {
        $docs = $this->driverDocumentService->getPendingDocuments();

        return response()->json([
            'message' => 'Pending documents retrieved successfully',
            'data' => $docs
        ]);
    }
}
