<?php

namespace App\Services;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\Trip\CreateTripDTO;
use App\DTO\Trip\UpdateTripDTO;
use App\Exceptions\InvalidDateTripUpdateException;
use DateTime;
use Spatie\LaravelData\Optional;
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

        $userTrips = $this->tripRepository->getTripsPeriod(
            $userId,
            $tripDto->start_date
        );

        if(!empty($userTrips)) {
            throw new UserHasTripInPeriod(
                'User already has an active trip in the sent period.',
                Response::HTTP_CONFLICT
            );
        }

        $this->tripRepository->create($tripDto);
    }

    public function getUserTrips(int $userId): Collection
    {
        return $this->tripRepository->getTrips($userId);
    }

    public function getUserTrip(int $tripId, int $userId): Trip
    {
        $trip = $this->tripRepository->getTrip($userId, $tripId);

        $this->validateTripExists($trip);

        return $trip;
    }

    public function updateTrip(array $tripData, int $tripId, int $userId): void
    {
        $trip = $this->tripRepository->getTrip($userId, $tripId);

        $this->validateTripExists($trip);

        if (isset($tripData['start_date'])) {
            $tripData['start_date'] = (new DateTime($tripData['start_date']))->format('Y-m-d H:i:s');
        }

        if (isset($tripData['end_date'])) {
            $tripData['end_date'] = (new DateTime($tripData['end_date']))->format('Y-m-d H:i:s');
        }

        $updateTripDto = UpdateTripDTO::from($tripData);

        if (!($updateTripDto->end_date instanceof Optional) && $updateTripDto->end_date < $trip->start_date) {
            throw new InvalidDateTripUpdateException(
                "End date can't be before trip's start date.",
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->tripRepository->update($updateTripDto, $tripId, $userId);
    }

    public function deleteTrip(int $tripId, int $userId): void
    {
        $trip = $this->tripRepository->getTrip($userId, $tripId);

        $this->validateTripExists($trip);

        $this->tripRepository->delete($tripId, $userId);
    }

    private function validateTripExists(?Trip $trip): void
    {
        if(empty($trip))
        {
            throw new TripNotFoundException('Trip was not found for the user.', Response::HTTP_NOT_FOUND);
        }
    }
}
