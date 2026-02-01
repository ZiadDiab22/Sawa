<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\RideDetailsRequest;
use App\Http\Requests\Ride\RideInfoRequest;
use App\Http\Requests\Ride\RideRequestDetailsRequest;
use App\Http\Resources\Ride\RideDetailsResource;
use App\Http\Resources\Ride\RideInfoResource;
use App\Services\Ride\RideInfoService;

class RideInfoController extends Controller
{
    public function __construct(
        protected RideInfoService $service
    ) {}

    public function index(RideInfoRequest $request)
    {
        return response()->json([
            'status' => true,
            'data' => RideInfoResource::collection($this->service->list($request->status)),
        ]);
    }

    public function show(RideDetailsRequest $request)
    {
        return response()->json([
            'status' => true,
            'data'   => new RideDetailsResource($this->service->getRideDetails($request->ride_id)),
        ]);
    }

    public function get(RideRequestDetailsRequest $request)
    {
        return response()->json([
            'status' => true,
            'data'   => $this->service->getRideRequestDetails($request->ride_request_id),
        ]);
    }
}
