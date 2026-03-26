<?php

namespace tests\Unit\app\Repositories;

use App\DTO\Trip\CreateTripDTO;
use App\DTO\Trip\UpdateTripDTO;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\TripRepository;

beforeEach(function () {
    $this->tripRepository = new TripRepository();
});

it('should create a trip in the database', function () {
    $user = User::factory(1)->create()->first();

    $tripDto = CreateTripDTO::from([
        'destination' => fake()->country(),
        'trip_name' => fake()->words(3, true),
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
        'user_id' => $user->id
    ]);

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
        'user_id'    => $user->id,
        'start_date' => now()->addDays(5),
        'end_date'   => now()->addMonth(),
    ]);

    $data = $this->tripRepository->getTripsPeriod(
        $user->id,
        now()->addDays(3)->format('Y-m-d H:i:s'),
        now()->addDays(10)->format('Y-m-d H:i:s'),
    );

    expect($data)->not->toBeEmpty();
    expect(count($data))->toBe(3);
});

it('should return empty when no trips exist in the period', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(3)->create([
        'user_id'    => $user->id,
        'start_date' => now()->addMonths(2),
        'end_date'   => now()->addMonths(3),
    ]);

    $data = $this->tripRepository->getTripsPeriod(
        $user->id,
        now()->addDay()->format('Y-m-d H:i:s'),
        now()->addDays(15)->format('Y-m-d H:i:s'),
    );

    expect($data)->toBeEmpty();
});

it('should return empty when new trip ends before existing trip starts', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(1)->create([
        'user_id'    => $user->id,
        'start_date' => now()->addMonths(2),
        'end_date'   => now()->addMonths(3),
    ]);

    $data = $this->tripRepository->getTripsPeriod(
        $user->id,
        now()->addDay()->format('Y-m-d H:i:s'),
        now()->addDays(15)->format('Y-m-d H:i:s'),
    );

    expect($data)->toBeEmpty();
});

it('should return empty when new trip starts after existing trip ends', function () {
    $user = User::factory(1)->create()->first();
    Trip::factory(1)->create([
        'user_id'    => $user->id,
        'start_date' => now()->addDay(),
        'end_date'   => now()->addDays(15),
    ]);

    $data = $this->tripRepository->getTripsPeriod(
        $user->id,
        now()->addMonths(2)->format('Y-m-d H:i:s'),
        now()->addMonths(3)->format('Y-m-d H:i:s'),
    );

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

it('should update all provided fields of a trip in the database', function () {
    $user = User::factory(1)->create()->first();
    $trip = Trip::factory(1)->create(['user_id' => $user->id])->first();

    $updateDto = UpdateTripDTO::from([
        'destination' => 'Updated Destination',
        'trip_name'   => 'Updated Name',
    ]);

    $this->tripRepository->update($updateDto, $trip->id, $user->id);

    $this->assertDatabaseHas('trips', [
        'id'          => $trip->id,
        'destination' => 'Updated Destination',
        'trip_name'   => 'Updated Name',
    ]);
});

it('should update only the destination without changing other fields', function () {
    $user = User::factory(1)->create()->first();
    $trip = Trip::factory(1)->create([
        'user_id'  => $user->id,
        'trip_name' => 'Original Name',
    ])->first();

    $updateDto = UpdateTripDTO::from(['destination' => 'New Destination']);

    $this->tripRepository->update($updateDto, $trip->id, $user->id);

    $this->assertDatabaseHas('trips', [
        'id'          => $trip->id,
        'destination' => 'New Destination',
        'trip_name'   => 'Original Name',
    ]);
});

it('should delete a trip from the database', function () {
    $user = User::factory(1)->create()->first();
    $trip = Trip::factory(1)->create(['user_id' => $user->id])->first();

    $this->tripRepository->delete($trip->id, $user->id);

    $this->assertSoftDeleted('trips', ['id' => $trip->id]);
});

it('should not delete a trip that belongs to another user', function () {
    $user      = User::factory(1)->create()->first();
    $otherUser = User::factory(1)->create()->first();
    $trip      = Trip::factory(1)->create(['user_id' => $user->id])->first();

    $this->tripRepository->delete($trip->id, $otherUser->id);

    $this->assertDatabaseHas('trips', ['id' => $trip->id]);
});