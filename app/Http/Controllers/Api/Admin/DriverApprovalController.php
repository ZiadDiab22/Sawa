<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DriverApprovalService;

class DriverApprovalController extends Controller
{
    public function __construct(
        protected DriverApprovalService $service
    ) {}

    // GET /admin/drivers/pending
    public function pendingDrivers()
    {
        return response()->json(
            $this->service->getPendingDrivers()
        );
    }

    // GET /admin/drivers/{id}
    public function show(int $id)
    {
        return response()->json(
            $this->service->getDriverDetails($id)
        );
    }

    // POST /admin/drivers/{id}/approve
    public function approve(int $id)
    {
        $this->service->approveDriver($id);

        return response()->json([
            'message' => 'Driver approved successfully'
        ]);
    }

    // POST /admin/drivers/{id}/reject
    public function reject(int $id)
    {
        $this->service->rejectDriver($id);

        return response()->json([
            'message' => 'Driver rejected successfully'
        ]);
    }

    // GET /admin/drivers/approved
public function approvedDrivers()
{
    return response()->json(
        $this->service->getApprovedDrivers()
    );
}

}
