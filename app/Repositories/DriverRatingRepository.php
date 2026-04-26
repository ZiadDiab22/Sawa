<?php

namespace App\Repositories;

use App\Models\DriverRating;
use Carbon\Carbon;

class DriverRatingRepository
{
  public function create(array $data)
  {
    return DriverRating::create($data);
  }

  public function update($id, array $data)
  {
    $rating = $this->findById($id);
    $rating->update($data);
    return $rating;
  }

  public function delete($id)
  {
    return DriverRating::destroy($id);
  }

  public function findById($id)
  {
    return DriverRating::findOrFail($id);
  }

  public function averageForDriverByDate(int $driverId, Carbon $date): float
  {
    return (float) DriverRating::query()
      ->where('driver_id', $driverId)
      ->whereDate('created_at', $date)
      ->avg('rating') ?? 0;
  }


  public function getDriverRatingsWithStats(int $driverId, int $perPage = 10): array
    {
        $baseQuery = DriverRating::query()
            ->where('driver_id', $driverId);

        return [
            'average' => (clone $baseQuery)->avg('rating') ?? 0,

            'ratings' => $baseQuery
                ->with('user:id,name')
                ->latest()
                ->paginate($perPage),
        ];
    }
}
