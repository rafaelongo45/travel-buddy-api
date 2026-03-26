<?php

namespace tests\Unit\app\Services;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\Contracts\Services\TripServiceInterface;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\TripInviteAlreadySent;
use App\Models\Trip;
use App\Models\TripParticipants;
use App\Services\TripParticipantsService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

it('should throw BadRequestHttpException when user tries to invite himself', function () {
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
})->throws(BadRequestHttpException::class);

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
        ->andReturn(new TripParticipants());

    $this->tripParticipantsService->inviteUser(
        ['user_id' => $guestId, 'trip_id' => $tripId],
        $userId
    );
})->throws(TripInviteAlreadySent::class);
