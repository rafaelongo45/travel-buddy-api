<?php

namespace App\DTO\TripParticipants;

use Spatie\LaravelData\Data;

class UpdateInviteDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $status
    ){}
}
