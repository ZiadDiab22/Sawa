<?php


namespace App\Repositories\Passenger;

use App\Models\PassengerRating;
use Carbon\Carbon;

class PassengerRatingRepository
{
    public function create(array $data)
    {
        return PassengerRating::query()->create($data);
    }

    public function update($id, array $data)
    {
        $rating = $this->findById($id);
        $rating->update($data);
        return $rating;
    }

    public function delete($id)
    {
        return PassengerRating::destroy($id);
    }

    public function findById($id)
    {
        return PassengerRating::query()->findOrFail($id);
    }


    public function getPassengerRatingsWithStats(int $passengerId, int $perPage = 10): array
    {
        $baseQuery = PassengerRating::query()
            ->where('user_id', $passengerId);

        return [
            'average' => (clone $baseQuery)->avg('rating') ?? 0,

            'ratings' => $baseQuery
                ->with('driver:id,name')
                ->latest()
                ->paginate($perPage),
        ];
    }
}
