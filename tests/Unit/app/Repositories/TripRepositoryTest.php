<?php

namespace tests\Unit\app\Repositories;

use App\DTO\Trip\CreateTripDTO;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\TripRepository;

beforeEach(function () {
    $this->tripRepository = new TripRepository();
});

it('should create a trip in the database', function () {
    $user = User::factory(1)->create()->first();

    $tripDto = CreateTripDTO::process([
        'destination' => fake()->country(),
        'trip_name' => fake()->words(3, true),
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
    ], $user->id);

    $this->tripRepository->create($tripDto);

    $this->assertDatabaseHas('trips', $tripDto->toArray());
});

it('should find user trips from database', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(3)->create([
        'user_id' => $user->id
    ]);

    $data = $this->tripRepository->getTrips($user->id);

    $this->assertCount(3, $data);
});

it('should find trips in a period from database', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(3)->create([
        'user_id' => $user->id,
        'end_date' => now()->addMonth(),
    ]);

    $data = $this->tripRepository->getTripsPeriod($user->id, now()->subDay()->format('Y-m-d'));

    expect($data)->not->toBeEmpty();
    expect(count($data))->toBe(3);
});

it('should return empty when no trips exist in the period', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(3)->create([
        'user_id' => $user->id,
        'end_date' => now()->subMonth(),
    ]);

    $data = $this->tripRepository->getTripsPeriod($user->id, now()->addDay()->format('Y-m-d'));

    expect($data)->toBeEmpty();
});

it('should find a trip by trip id and user id', function () {
    $user = User::factory(1)->create()->first();
    $trip = Trip::factory(1)->create([
        'user_id' => $user->id
    ])->first();

    $data = $this->tripRepository->getTrip($user->id, $trip->id);

    expect($data->id)->toBe($trip->id);
    expect($data->user_id)->toBe($user->id);
});

it('should return null when trip is not found', function () {
    $user = User::factory(1)->create()->first();

    $data = $this->tripRepository->getTrip($user->id, 999);

    expect($data)->toBeNull();
});