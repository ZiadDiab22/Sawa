<?php

namespace App\Services\User\Driver;

use App\Repositories\DriverRatingRepository;

class DriverRatingService
{
  public function __construct(
    protected DriverRatingRepository $repository,
  ) {}

  public function store(array $data)
  {
    return $this->repository->create($data);
  }

  public function update($id, array $data)
  {
    return $this->repository->update($id, $data);
  }

  public function delete($id)
  {
    return $this->repository->delete($id);
  }
}
