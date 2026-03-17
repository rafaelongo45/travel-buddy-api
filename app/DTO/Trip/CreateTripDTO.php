<?php

namespace App\DTO\Trip;

use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class CreateTripDTO extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $destination,
        public string $trip_name,
        public string $start_date,
        public string $end_date,
        public int $user_id,
    ){}

    public static function process(array $data, int $userId): self
    {
        return self::from([
            'destination' => $data['destination'],
            'trip_name' => $data['trip_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'user_id' => $userId
        ]);
    }
}
