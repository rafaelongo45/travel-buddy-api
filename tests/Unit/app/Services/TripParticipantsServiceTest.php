<?php

namespace tests\Unit\app\Services;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\Exceptions\CannotInviteYourselfException;
use App\Exceptions\InvalidUserTripInvite;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\TripInvitationNotFound;
use App\Exceptions\TripInviteAlreadySent;
use App\Exceptions\TripInviteStatusAlreadyUpdated;
use App\Models\Trip;
use App\Models\TripParticipant;
use App\Services\TripParticipantsService;
use Mockery;

beforeEach(function () {
    $this->tripService                = Mockery::mock(TripServiceInterface::class);
    $this->tripParticipantsRepository = Mockery::mock(TripParticipantsRepositoryInterface::class);
    $this->tripParticipantsService    = new TripParticipantsService(
        $this->tripService,
        $this->tripParticipantsRepository
    );
});

afterEach(function () {
    Mockery::close();
});

it('should invite a user successfully', function () {
    $tripId  = 1;
    $userId  = 1;
    $guestId = 2;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andReturn(new Trip());

    $this->tripParticipantsRepository
        ->shouldReceive('findInvite')
        ->once()
        ->with($guestId, $tripId)
        ->andReturn(null);

    $this->tripParticipantsRepository
        ->shouldReceive('create')
        ->once();

    $this->tripParticipantsService->inviteUser(
        ['user_id' => $guestId, 'trip_id' => $tripId],
        $userId
    );

    expect(true)->toBeTrue();
});

it('should throw TripNotFoundException when trip does not belong to the user', function () {
    $tripId = 99;
    $userId = 1;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andThrow(new TripNotFoundException('Trip was not found for the user.', 404));

    $this->tripParticipantsService->inviteUser(
        ['user_id' => 2, 'trip_id' => $tripId],
        $userId
    );
})->throws(TripNotFoundException::class);

it('should throw CannotInviteYourselfException when user tries to invite himself', function () {
    $tripId = 1;
    $userId = 1;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andReturn(new Trip());

    $this->tripParticipantsService->inviteUser(
        ['user_id' => $userId, 'trip_id' => $tripId],
        $userId
    );
})->throws(CannotInviteYourselfException::class);

it('should throw TripInviteAlreadySent when invite was already sent', function () {
    $tripId  = 1;
    $userId  = 1;
    $guestId = 2;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andReturn(new Trip());

    $this->tripParticipantsRepository
        ->shouldReceive('findInvite')
        ->once()
        ->with($guestId, $tripId)
        ->andReturn(new TripParticipant());

    $this->tripParticipantsService->inviteUser(
        ['user_id' => $guestId, 'trip_id' => $tripId],
        $userId
    );
})->throws(TripInviteAlreadySent::class);

it('should return a collection of invites for a trip', function () {
    $tripId = 1;
    $userId = 1;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andReturn(new Trip());

    $this->tripParticipantsRepository
        ->shouldReceive('getInvites')
        ->once()
        ->with($tripId)
        ->andReturn(collect([]));

    $result = $this->tripParticipantsService->getAllTripInvites($tripId, $userId);

    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('should throw TripNotFoundException when trip does not belong to user on getInvites', function () {
    $tripId = 99;
    $userId = 1;

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with($tripId, $userId)
        ->andThrow(new TripNotFoundException('Trip was not found for the user.', 404));

    $this->tripParticipantsRepository
        ->shouldReceive('getInvites')
        ->never();

    $this->tripParticipantsService->getAllTripInvites($tripId, $userId);
})->throws(TripNotFoundException::class);

// updateInviteStatus

it('should update invite status successfully', function () {
    $inviteId = 1;
    $userId   = 2;

    $invite          = new TripParticipant();
    $invite->id      = $inviteId;
    $invite->user_id = $userId;
    $invite->status  = 'pending';

    $this->tripParticipantsRepository
        ->shouldReceive('getInvite')
        ->once()
        ->with($inviteId)
        ->andReturn($invite);

    $this->tripParticipantsRepository
        ->shouldReceive('updateInvite')
        ->once();

    $this->tripParticipantsService->updateInviteStatus(
        ['invite_id' => $inviteId, 'status' => 'accepted'],
        $userId
    );

    expect(true)->toBeTrue();
});

it('should throw TripInvitationNotFound when invite does not exist', function () {
    $this->tripParticipantsRepository
        ->shouldReceive('getInvite')
        ->once()
        ->andReturn(null);

    $this->tripParticipantsRepository
        ->shouldReceive('updateInvite')
        ->never();

    $this->tripParticipantsService->updateInviteStatus(
        ['invite_id' => 99, 'status' => 'accepted'],
        1
    );
})->throws(TripInvitationNotFound::class);

it('should throw InvalidUserTripInvite when user is not the invited user', function () {
    $invite          = new TripParticipant();
    $invite->user_id = 99;
    $invite->status  = 'pending';

    $this->tripParticipantsRepository
        ->shouldReceive('getInvite')
        ->once()
        ->andReturn($invite);

    $this->tripParticipantsRepository
        ->shouldReceive('updateInvite')
        ->never();

    $this->tripParticipantsService->updateInviteStatus(
        ['invite_id' => 1, 'status' => 'accepted'],
        1
    );
})->throws(InvalidUserTripInvite::class);

it('should throw TripInviteStatusAlreadyUpdated when invite is not pending', function () {
    $userId          = 1;
    $invite          = new TripParticipant();
    $invite->user_id = $userId;
    $invite->status  = 'accepted';

    $this->tripParticipantsRepository
        ->shouldReceive('getInvite')
        ->once()
        ->andReturn($invite);

    $this->tripParticipantsRepository
        ->shouldReceive('updateInvite')
        ->never();

    $this->tripParticipantsService->updateInviteStatus(
        ['invite_id' => 1, 'status' => 'refused'],
        $userId
    );
})->throws(TripInviteStatusAlreadyUpdated::class);
