<?php

namespace App\Repositories;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\DTO\TripParticipants\InviteUserDto;
use App\Models\TripParticipants;

class TripParticipantsRepository implements TripParticipantsRepositoryInterface
{
    public function create(InviteUserDto $inviteUserDto): void
    {
        TripParticipants::create($inviteUserDto->toArray());
    }

    public function findInvite(int $userId, int $tripId): ?TripParticipants
    {
        return TripParticipants::where('user_id', $userId)
            ->where('trip_id', $tripId)
            ->first();
    }
}
