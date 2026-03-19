<?php

namespace App\Services;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\Trip\CreateTripDTO;
use App\Exceptions\Trip\UserHasTripInPeriod;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class TripService implements TripServiceInterface
{
    public function __construct(
        private readonly TripRepositoryInterface $tripRepository
    ){}

    public function createTrip(array $tripData): void
    {
        $user_id = auth()->guard('api')->user()->id;

        $tripDto = CreateTripDTO::process($tripData, $user_id);

        $user_trips = $this->tripRepository->getTripsPeriod(
            $user_id,
            $tripDto->start_date
        );

        if(!empty($user_trips))
        {
            throw new UserHasTripInPeriod(
                'User already has an active trip in the sent period.',
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->tripRepository->create($tripDto);
    }

    public function getUserTrips(): Collection
    {
        $trips =  $this->tripRepository->getTrips(auth()->guard('api')->user()->id);

        return new Collection($trips);
    }
}
