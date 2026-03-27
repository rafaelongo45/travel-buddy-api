<?php

namespace tests\Unit\app\Repositories;

use App\DTO\TripParticipants\InviteUserDto;
use App\Models\Trip;
use App\Models\TripParticipant;
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

    TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $result = $this->repository->findInvite($guest->id, $trip->id);

    expect($result)->toBeInstanceOf(TripParticipant::class);
    expect($result->user_id)->toBe($guest->id);
    expect($result->trip_id)->toBe($trip->id);
});

it('should return null when invite does not exist', function () {
    $result = $this->repository->findInvite(99999, 99999);

    expect($result)->toBeNull();
});

it('should return all invites for a trip', function () {
    $owner = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $owner->id]);

    TripParticipant::factory()->count(3)->create([
        'trip_id' => $trip->id,
    ]);

    $result = $this->repository->getInvites($trip->id);

    expect($result)->toHaveCount(3);
    expect($result->first()->relationLoaded('user'))->toBeTrue();
});

it('should return empty collection when trip has no invites', function () {
    $owner = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $owner->id]);

    $result = $this->repository->getInvites($trip->id);

    expect($result)->toBeEmpty();
});
