<?php

namespace tests\Unit\app\Repositories;

use App\DTO\TripParticipants\InviteUserDto;
use App\DTO\TripParticipants\UpdateInviteDto;
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

it('should get a single invite by id', function () {
    $owner  = User::factory()->create();
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $owner->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $result = $this->repository->getInvite($invite->id);

    expect($result)->toBeInstanceOf(TripParticipant::class);
    expect($result->id)->toBe($invite->id);
});

it('should return null when invite id does not exist', function () {
    $result = $this->repository->getInvite(99999);

    expect($result)->toBeNull();
});

it('should soft delete an invite', function () {
    $owner  = User::factory()->create();
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $owner->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->repository->delete($invite->id);

    $this->assertSoftDeleted('trip_participants', ['id' => $invite->id]);
});

it('should return accepted trips for a user', function () {
    $owner        = User::factory()->create();
    $user         = User::factory()->create();
    $acceptedTrip = Trip::factory()->create(['user_id' => $owner->id]);
    $pendingTrip  = Trip::factory()->create(['user_id' => $owner->id]);

    TripParticipant::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $acceptedTrip->id,
        'status'  => 'accepted',
    ]);

    TripParticipant::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $pendingTrip->id,
        'status'  => 'pending',
    ]);

    $result = $this->repository->getUserTripsInvites($user->id);

    expect($result)->toHaveCount(1);
    expect($result->first()->trip_id)->toBe($acceptedTrip->id);
    expect($result->first()->relationLoaded('trip'))->toBeTrue();
});

it('should return empty collection when user has no accepted trips', function () {
    $user = User::factory()->create();

    $result = $this->repository->getUserTripsInvites($user->id);

    expect($result)->toBeEmpty();
});

it('should not return trips from other users', function () {
    $owner    = User::factory()->create();
    $userA    = User::factory()->create();
    $userB    = User::factory()->create();
    $trip     = Trip::factory()->create(['user_id' => $owner->id]);

    TripParticipant::factory()->create([
        'user_id' => $userA->id,
        'trip_id' => $trip->id,
        'status'  => 'accepted',
    ]);

    $result = $this->repository->getUserTripsInvites($userB->id);

    expect($result)->toBeEmpty();
});

it('should update invite status', function () {
    $owner  = User::factory()->create();
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $owner->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
        'status'  => 'pending',
    ]);

    $dto = UpdateInviteDto::from(['status' => 'accepted']);

    $this->repository->updateInvite($dto, $invite->id);

    $this->assertDatabaseHas('trip_participants', [
        'id'     => $invite->id,
        'status' => 'accepted',
    ]);
});
