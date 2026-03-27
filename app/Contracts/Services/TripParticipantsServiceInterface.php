<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface TripParticipantsServiceInterface
{
    public function inviteUser(array $inviteData, int $userId): void;

    public function getAllTripInvites(int $tripId, int $userId): Collection;
}
