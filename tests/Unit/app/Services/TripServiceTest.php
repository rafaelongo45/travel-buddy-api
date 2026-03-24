<?php

namespace tests\Unit\app\Services;

use App\Contracts\Repositories\TripRepositoryInterface;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\Trip\UserHasTripInPeriod;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function () {
    $this->tripRepository = Mockery::mock(TripRepositoryInterface::class);
    $this->tripService = new TripService($this->tripRepository);
});

afterEach(function () {
    Mockery::close();
});

it('should create a trip successfully', function () {
    $tripData = [
        'destination' => 'Brazil',
        'trip_name'   => 'Summer Trip',
        'start_date'  => '2027-01-01',
        'end_date'    => '2027-01-15',
    ];

    $this->tripRepository
        ->shouldReceive('getTripsPeriod')
        ->once()
        ->andReturn([]);

    $this->tripRepository
        ->shouldReceive('create')
        ->once();

    $this->tripService->createTrip($tripData, userId: 1);

    expect(true)->toBeTrue();
});

it('should throw UserHasTripInPeriod when user has an active trip in the period', function () {
    $tripData = [
        'destination' => 'Brazil',
        'trip_name'   => 'Summer Trip',
        'start_date'  => '2027-01-01',
        'end_date'    => '2027-01-15',
    ];

    $this->tripRepository
        ->shouldReceive('getTripsPeriod')
        ->once()
        ->andReturn([new Trip()]);

    $this->tripService->createTrip($tripData, userId: 1);
})->throws(UserHasTripInPeriod::class);

it('should return all trips for a user as a collection', function () {
    $userId = 1;

    $this->tripRepository
        ->shouldReceive('getTrips')
        ->once()
        ->with($userId)
        ->andReturn(new Collection([new Trip(), new Trip()]));

    $trips = $this->tripService->getUserTrips($userId);

    expect($trips)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
});

it('should return an empty collection when user has no trips', function () {
    $userId = 1;

    $this->tripRepository
        ->shouldReceive('getTrips')
        ->once()
        ->with($userId)
        ->andReturn(new Collection());

    $trips = $this->tripService->getUserTrips($userId);

    expect($trips)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('should return a trip by id for a user', function () {
    $tripId = 1;
    $userId = 1;

    $this->tripRepository
        ->shouldReceive('getTrip')
        ->once()
        ->with($userId, $tripId)
        ->andReturn(new Trip());

    $trip = $this->tripService->getUserTrip($tripId, $userId);

    expect($trip)->toBeInstanceOf(Trip::class);
});

it('should throw TripNotFoundException when trip is not found', function () {
    $tripId = 99;
    $userId = 1;

    $this->tripRepository
        ->shouldReceive('getTrip')
        ->once()
        ->with($userId, $tripId)
        ->andReturn(null);

    $this->tripService->getUserTrip($tripId, $userId);
})->throws(TripNotFoundException::class);
