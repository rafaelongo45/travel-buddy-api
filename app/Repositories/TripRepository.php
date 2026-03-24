<?php

namespace App\Repositories;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\DTO\Trip\CreateTripDTO;
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
        return Trip::where([
            'user_id' => $userId
        ])->get();
    }

    public function getTripsPeriod(int $user_id, string $start_date): ?array
    {
        $date = new DateTime($start_date);
        
        return Trip::where('user_id', '=', $user_id)
        ->where('end_date', '>=', $date->format('Y-m-d H:i:s'))
        ->get()
        ->all();
    }

    public function getTrip(int $user_id, int $trip_id): ?Trip
    {
        return Trip::where('user_id', '=', $user_id)
        ->where('id', '=', $trip_id)
        ->first();
    }
}
