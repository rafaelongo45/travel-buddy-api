<?php

namespace App\Contracts\Repositories;

use App\DTO\TripParticipants\InviteUserDto;
use App\Models\TripParticipants;

interface TripParticipantsRepositoryInterface
{
    public function create(InviteUserDto $inviteUserDto): void;

    public function findInvite(int $userId, int $tripId): ?TripParticipants;
}
