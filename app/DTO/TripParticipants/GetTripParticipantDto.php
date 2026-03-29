<?php

namespace App\DTO\TripParticipants;

use Spatie\LaravelData\Data;

class GetTripParticipantDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public int $id,
        public string $name,
    ){}
}
