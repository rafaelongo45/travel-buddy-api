<?php

namespace App\Contracts\Repositories;

use App\Models\Trip;
use App\DTO\Trip\CreateTripDTO;
use Illuminate\Support\Collection;

interface TripRepositoryInterface
{
    public function create(CreateTripDTO $tripDto): void;

    public function getTrips(int $userId): Collection;

    public function getTripsPeriod(int $user_id, string $start_date): ?array;

    public function getTrip(int $user_id, int $trip_id): ?Trip;
}
