<?php

namespace App\Services;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\Trip\CreateTripDTO;
use Illuminate\Support\Collection;

class TripService implements TripServiceInterface
{
    public function __construct(
        private readonly TripRepositoryInterface $tripRepository
    ){}

    public function createTrip(array $tripData): void
    {
        $tripDto = CreateTripDTO::process($tripData, auth()->guard('api')->user()->id);

        $this->tripRepository->create($tripDto);

    }

    public function getUserTrips(): Collection
    {
        $trips =  $this->tripRepository->getTrips(auth()->guard('api')->user()->id);

        return new Collection($trips);
    }
}
