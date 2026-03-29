<?php

namespace App\DTO\TripParticipants;

use App\DTO\Trip\TripDto;
use Spatie\LaravelData\Data;

class UserTripParticipationDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public TripDto $trip,
        public string $status
    ){}
}
