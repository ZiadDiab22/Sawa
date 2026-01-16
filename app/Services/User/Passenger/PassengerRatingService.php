<?php


namespace App\Services\User\Passenger;

use App\Repositories\Passenger\PassengerRatingRepository;

class PassengerRatingService
{
    public function __construct(
        protected PassengerRatingRepository $repository,
    )
    {
    }

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
