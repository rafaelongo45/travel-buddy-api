<?php

namespace App\DTO\Trip;

use Spatie\LaravelData\Data;

class TripDto extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $destination,
        public string $trip_name,
        public string $start_date,
        public string $end_date,
    ){}
}
