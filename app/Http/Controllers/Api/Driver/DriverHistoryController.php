<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\User\Driver\DriverHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverHistoryController extends Controller
{
    public function __construct(
        private DriverHistoryService $service
    ) {}

    public function index()
    {
        return response()->json([
            'status' => true,
            'data'   => $this->service->handle(Auth::id()),
        ]);
    }
}
