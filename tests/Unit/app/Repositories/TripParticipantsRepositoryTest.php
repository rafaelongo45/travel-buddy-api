<?php

namespace tests\Unit\app\Repositories;

use App\DTO\TripParticipants\InviteUserDto;
use App\Models\Trip;
use App\Models\TripParticipants;
use App\Models\User;
use App\Repositories\TripParticipantsRepository;

beforeEach(function () {
    $this->repository = new TripParticipantsRepository();
});

it('should create an invite in the database', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $owner->id]);

    $dto = InviteUserDto::from([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->repository->create($dto);

    $this->assertDatabaseHas('trip_participants', [
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
        'status'  => 'pending',
    ]);
});

it('should find an existing invite', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $owner->id]);

    TripParticipants::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $result = $this->repository->findInvite($guest->id, $trip->id);

    expect($result)->toBeInstanceOf(TripParticipants::class);
    expect($result->user_id)->toBe($guest->id);
    expect($result->trip_id)->toBe($trip->id);
});

it('should return null when invite does not exist', function () {
    $result = $this->repository->findInvite(99999, 99999);

    expect($result)->toBeNull();
});
