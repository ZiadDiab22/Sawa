<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverRatingRequest;
use App\Http\Requests\Driver\UpdateDriverRatingRequest;
use App\Models\DriverRating;
use App\Models\Ride;
use App\Services\User\Driver\DriverRatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverRatingController extends Controller
{
    public function __construct(
        protected DriverRatingService $service
    ) {}

    public function store(StoreDriverRatingRequest $request)
    {
        $ride = Ride::findOrFail($request->ride_id);

        $userId = Auth::id();

        if (DriverRating::where('ride_id', $ride->id)->where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'You have already submitted a rating for this ride.'
            ], 409);
        }

        $data = $request->validated();
        $data['user_id'] = Auth::user()->id;
        $data['driver_id'] = $ride->driver_id;

        $rating = $this->service->store($data);
        $rating->makeHidden('user');

        return response()->json($rating, 201);
    }

    public function update(UpdateDriverRatingRequest $request, $id)
    {
        $rating = $this->service->update($id, $request->validated());
        $rating->makeHidden('user');
        return response()->json($rating);
    }

    public function destroy($id)
    {
        $rating = DriverRating::where('id', $id)->exists();
        if (!$rating) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong id',
            ], 403);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Deleted successfully']);
    }
}
