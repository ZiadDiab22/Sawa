<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\DriverDocumentService;
use Illuminate\Http\Request;

class DriverDocumentController extends Controller
{
    public function __construct(
        protected DriverDocumentService $service
    ) {}

    // GET /api/driver/documents
    public function index()
    {
        return response()->json(
            $this->service->list(auth()->id())
        );
    }

    // POST /api/driver/documents
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:license,id_card,insurance',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'expires_at' => 'nullable|date|after:today',
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $this->service->upload(auth()->id(), $request->all())
        ]);
    }
}
