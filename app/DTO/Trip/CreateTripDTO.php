<?php

namespace App\DTO\Trip;

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
}
