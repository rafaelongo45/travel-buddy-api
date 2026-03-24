<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;
use App\Models\Trip;

interface TripServiceInterface
{
    public function createTrip(array $tripData, int $userId): void;

    /**
     * @return Collection<Trip>
     */
    public function getUserTrips(int $userId): Collection;

    public function getUserTrip(int $tripId, int $userId): Trip;
}
