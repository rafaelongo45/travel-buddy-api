<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;
use App\Models\Trip;

interface TripServiceInterface
{
    public function createTrip(array $tripData): void;

    /**
     * @return Collection<Trip>
     */
    public function getUserTrips(): Collection;

    public function getUserTrip(int $trip_id): Trip;
}
