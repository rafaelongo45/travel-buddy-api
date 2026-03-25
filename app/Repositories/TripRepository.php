<?php

namespace App\Repositories;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\DTO\Trip\CreateTripDTO;
use App\DTO\Trip\UpdateTripDTO;
use App\Models\Trip;
use DateTime;
use Illuminate\Support\Collection;

class TripRepository implements TripRepositoryInterface
{
    public function create(CreateTripDTO $tripDto): void
    {
        Trip::create($tripDto->toArray());
    }

    public function getTrips(int $userId): Collection
    {
        return Trip::where('user_id', $userId)->get();
    }

    public function getTripsPeriod(int $userId, string $start_date): ?array
    {
        $date = new DateTime($start_date);
        
        return Trip::where('user_id', $userId)
        ->where('end_date', '>=', $date->format('Y-m-d H:i:s'))
        ->get()
        ->all();
    }

    public function getTrip(int $userId, int $tripId): ?Trip
    {
        return Trip::where('user_id', $userId)
        ->where('id', $tripId)
        ->first();
    }

    public function update(UpdateTripDTO $tripData, int $tripId, int $userId): void
    {
        Trip::where('id', $tripId)
        ->where('user_id', $userId)
        ->update($tripData->toArray());
    }

    public function delete(int $tripId, int $userId): void
    {
        Trip::where('id', $tripId)
        ->where('user_id', $userId)
        ->delete();
    }
}
