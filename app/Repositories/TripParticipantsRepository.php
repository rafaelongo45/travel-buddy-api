<?php

namespace App\Repositories;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\DTO\TripParticipants\InviteUserDto;
use App\Models\TripParticipant;
use Illuminate\Support\Collection;

class TripParticipantsRepository implements TripParticipantsRepositoryInterface
{
    public function create(InviteUserDto $inviteUserDto): void
    {
        TripParticipant::create($inviteUserDto->toArray());
    }

    public function findInvite(int $userId, int $tripId): ?TripParticipant
    {
        return TripParticipant::where('user_id', $userId)
            ->where('trip_id', $tripId)
            ->first();
    }

    public function getInvites(int $tripId): Collection
    {
        return TripParticipant::with('user')
            ->where('trip_id', $tripId)
            ->get();
    }
}
