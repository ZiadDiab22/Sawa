<?php

namespace App\Repositories;

use App\Models\DriverRating;

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
}
