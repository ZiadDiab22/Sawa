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
<<<<<<< HEAD
    public function getPassengerRatingsWithStats(int $passengerId, int $perPage = 10)
    {
        return $this->repository->getPassengerRatingsWithStats($passengerId, $perPage);
    }

=======

     public function getPassengerRatingsWithStats(int $passengerId, int $perPage = 10)
    {
        return $this->repository->getPassengerRatingsWithStats($passengerId, $perPage);
    }
>>>>>>> 22046366b20c75e68fb78d9e08864aa4615280a3
}
