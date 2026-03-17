<?php

namespace App\Contracts\Repositories;

use App\DTO\Trip\CreateTripDTO;
use App\Models\Trip;
use Illuminate\Support\Collection;

interface TripRepositoryInterface
{
    public function create(CreateTripDTO $tripDto): void;

    public function getTrips(int $userId): Collection;
}
