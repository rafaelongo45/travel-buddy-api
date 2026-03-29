<?php

namespace App\DTO\TripParticipants;

use Spatie\LaravelData\Data;

class GetAllTripParticipantsDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public GetTripParticipantDto $user,
        public string $status
    ){}
}
