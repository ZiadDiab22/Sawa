<?php

namespace App\Http\Controllers\Api\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverRatingRequest;
use App\Http\Requests\Driver\UpdateDriverRatingRequest;
use App\Models\PassengerRating;
use App\Models\Ride;
use App\Services\User\Driver\DriverRatingService;
use App\Services\User\Passenger\PassengerRatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PassengerRatingController extends Controller
{
    public function __construct(
        protected PassengerRatingService $service
    ) {}
    public function store(StoreDriverRatingRequest $request)
    {
        $ride = Ride::query()->findOrFail($request->ride_id);

        $driverId = Auth::id();

        if (PassengerRating::query()->where('ride_id', $ride->id)->where('driver_id', $driverId)->exists()) {
            return response()->json([
                'message' => 'You have already submitted a rating for this ride.'
            ], 409);
        }

        $data = $request->validated();
        $data['driver_id'] = Auth::user()->id;
        $data['user_id'] = $ride->user_id;

        $rating = $this->service->store($data);
        $rating->makeHidden('driver');

        return response()->json($rating, 201);
    }

    public function update(UpdateDriverRatingRequest $request, $id)
    {
        $rating = PassengerRating::query()->where('id', $id)->exists();
        if (!$rating) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong id',
            ], 403);
        }

        $rating = $this->service->update($id, $request->validated());
        $rating->makeHidden('driver');
        return response()->json($rating);
    }

    public function destroy($id)
    {
        $rating = PassengerRating::query()->where('id', $id)->exists();
        if (!$rating) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong id',
            ], 403);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Deleted successfully']);
    }

     public function index(Request $request, $passengerId)
{
    $perPage = $request->get('per_page', 10);

    $result = $this->service->getPassengerRatingsWithStats($passengerId, $perPage);

    $ratings = $result['ratings'];

    return response()->json([
        'passenger_id' => (int) $passengerId,

        'summary' => [
            'average_rating' => round($result['average'], 1),
            'total_reviews' => $ratings->total(),
        ],

        'reviews' => $ratings->items(),

        'pagination' => [
            'current_page' => $ratings->currentPage(),
            'last_page' => $ratings->lastPage(),
            'per_page' => $ratings->perPage(),
        ]
    ]);
}
}
