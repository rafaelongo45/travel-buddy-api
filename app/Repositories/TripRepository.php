<?php

namespace App\Repositories;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\DTO\Trip\CreateTripDTO;
use App\Models\Trip;
use Illuminate\Support\Collection;

class TripRepository implements TripRepositoryInterface
{
    public function create(CreateTripDTO $tripDto): void
    {
        Trip::create($tripDto->toArray());
    }

    public function getTrips(int $userId): Collection
    {
        return Trip::where([
            'user_id' => $userId
        ])->get();
    }
}
