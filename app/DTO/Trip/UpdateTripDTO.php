<?php

namespace App\DTO\Trip;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateTripDTO extends Data
{
    public function __construct(
        public string|Optional $destination,
        public string|Optional $trip_name,
        public string|Optional $start_date,
        public string|Optional $end_date,
    ){}
}
