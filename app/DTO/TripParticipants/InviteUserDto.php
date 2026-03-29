<?php

namespace App\DTO\TripParticipants;

use Spatie\LaravelData\Data;

class InviteUserDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public int $trip_id,
        public int $user_id,
        public string $status = 'pending'
    ){}
}
