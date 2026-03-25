<?php

namespace App\Contracts\Repositories;

use App\Models\Trip;
use App\DTO\Trip\CreateTripDTO;
use App\DTO\Trip\UpdateTripDTO;
use Illuminate\Support\Collection;

interface TripRepositoryInterface
{
    public function create(CreateTripDTO $tripDto): void;

    public function getTrips(int $userId): Collection;

    public function getTripsPeriod(int $userId, string $start_date): ?array;

    public function getTrip(int $userId, int $tripId): ?Trip;

    public function update(UpdateTripDTO $tripData, int $tripId, int $userId): void;

    public function delete(int $tripId,  int $userId): void;
}
