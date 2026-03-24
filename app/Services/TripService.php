<?php

namespace App\Services;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\Trip\CreateTripDTO;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\Trip\UserHasTripInPeriod;
use App\Models\Trip;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class TripService implements TripServiceInterface
{
    public function __construct(
        private readonly TripRepositoryInterface $tripRepository
    ){}

    public function createTrip(array $tripData, int $userId): void
    {
        $tripDto = CreateTripDTO::process($tripData, $userId);

        $user_trips = $this->tripRepository->getTripsPeriod(
            $userId,
            $tripDto->start_date
        );

        if(!empty($user_trips))
        {
            throw new UserHasTripInPeriod(
                'User already has an active trip in the sent period.',
                Response::HTTP_CONFLICT
            );
        }

        $this->tripRepository->create($tripDto);
    }

    public function getUserTrips(int $userId): Collection
    {
        $trips =  $this->tripRepository->getTrips($userId);

        return new Collection($trips);
    }

    public function getUserTrip(int $tripId, int $userId): Trip
    {
        $trip = $this->tripRepository->getTrip($userId, $tripId);

        if(empty($trip))
        {
            throw new TripNotFoundException('Trip was not found for the user.', Response::HTTP_NOT_FOUND);
        }

        return $trip;
    }
}
