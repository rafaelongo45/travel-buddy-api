<?php

namespace App\Contracts\Services;

interface TripParticipantsServiceInterface
{
    public function inviteUser(array $inviteData, int $userId): void;
}
