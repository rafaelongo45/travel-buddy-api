<?php

namespace App\Contracts\Repositories;

use App\DTO\TripParticipants\InviteUserDto;
use App\Models\TripParticipant;
use Illuminate\Support\Collection;

interface TripParticipantsRepositoryInterface
{
    public function create(InviteUserDto $inviteUserDto): void;

    public function findInvite(int $userId, int $tripId): ?TripParticipant;

    public function getInvites(int $tripId): Collection;
}
