<?php

namespace App\DTO\Trip;

use DateTime;
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
        $start_date = new DateTime($data['start_date']);
        $end_date = new DateTime($data['end_date']);

        return self::from([
            'destination' => $data['destination'],
            'trip_name' => $data['trip_name'],
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'user_id' => $userId
        ]);
    }
}
