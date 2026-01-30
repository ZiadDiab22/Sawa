<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\User\Driver\DriverLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverLocationController extends Controller
{
    public function __construct(private DriverLocationService $service) {}

    public function index()
    {
        $locations = $this->service->getAllLocations();

        return response()->json([
            'status' => true,
            'data' => $locations
        ]);
    }

    public function show()
    {
        $location = $this->service->show(Auth::id());

        return response()->json([
            'status' => true,
            'data' => $location
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'lat'       => 'required|numeric',
            'lng'       => 'required|numeric',
        ]);

        $location = $this->service->storeOrUpdateLocation(
            $request->driver_id,
            $request->lat,
            $request->lng
        );

        return response()->json([
            'status' => true,
            'data' => $location
        ]);
    }
}
