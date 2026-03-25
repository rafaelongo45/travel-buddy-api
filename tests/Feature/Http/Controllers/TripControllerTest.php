<?php

namespace tests\Feature\Http\Controllers;

use App\Contracts\Services\TripServiceInterface;
use App\Exceptions\InvalidDateTripUpdateException;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\Trip\UserHasTripInPeriod;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->tripService = Mockery::mock(TripServiceInterface::class);
    $this->app->instance(TripServiceInterface::class, $this->tripService);
    $this->user = User::factory()->create();
});

afterEach(function () {
    Mockery::close();
});

// --- createTrip ---

it('should create a trip and return 201', function () {
    $this->tripService
        ->shouldReceive('createTrip')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/', [
            'destination' => 'Brazil',
            'trip_name'   => 'Summer Trip',
            'start_date'  => now()->addDay()->format('Y-m-d'),
            'end_date'    => now()->addMonth()->format('Y-m-d'),
        ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'Trip created successfully.']);
});

it('should return 401 when creating trip without authentication', function () {
    $response = $this->postJson('/api/trips/', [
        'destination' => 'Brazil',
        'trip_name'   => 'Summer Trip',
        'start_date'  => now()->addDay()->format('Y-m-d'),
        'end_date'    => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertStatus(401);
});

it('should return 422 when required create trip fields are missing', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/', []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['destination', 'trip_name', 'start_date', 'end_date']);
});

it('should return 422 when start_date is in the past', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/', [
            'destination' => 'Brazil',
            'trip_name'   => 'Summer Trip',
            'start_date'  => now()->subDay()->format('Y-m-d'),
            'end_date'    => now()->addMonth()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['start_date']);
});

it('should return 422 when end_date is before start_date on create', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/', [
            'destination' => 'Brazil',
            'trip_name'   => 'Summer Trip',
            'start_date'  => now()->addMonth()->format('Y-m-d'),
            'end_date'    => now()->addDay()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['end_date']);
});

it('should return 409 when user already has a trip in the period', function () {
    $this->tripService
        ->shouldReceive('createTrip')
        ->once()
        ->andThrow(new UserHasTripInPeriod(
            'User already has an active trip in the sent period.',
            Response::HTTP_CONFLICT
        ));

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/', [
            'destination' => 'Brazil',
            'trip_name'   => 'Summer Trip',
            'start_date'  => now()->addDay()->format('Y-m-d'),
            'end_date'    => now()->addMonth()->format('Y-m-d'),
        ]);

    $response->assertStatus(409);
});

// --- getUserTrips ---

it('should return all trips for the authenticated user', function () {
    $this->tripService
        ->shouldReceive('getUserTrips')
        ->once()
        ->andReturn(new Collection([new Trip(), new Trip()]));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/trips/');

    $response->assertStatus(200)
             ->assertJsonCount(2);
});

it('should return 401 when listing trips without authentication', function () {
    $response = $this->getJson('/api/trips/');

    $response->assertStatus(401);
});

it('should return empty collection when user has no trips', function () {
    $this->tripService
        ->shouldReceive('getUserTrips')
        ->once()
        ->andReturn(new Collection());

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/trips/');

    $response->assertStatus(200)
             ->assertJsonCount(0);
});

// --- getUserTrip ---

it('should return a trip by id', function () {
    $trip = Trip::factory()->make(['id' => 1]);

    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->with(1, $this->user->id)
        ->andReturn($trip);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/trips/1');

    $response->assertStatus(200);
});

it('should return 404 when trip is not found', function () {
    $this->tripService
        ->shouldReceive('getUserTrip')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found.', Response::HTTP_NOT_FOUND));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/trips/99');

    $response->assertStatus(404);
});

// --- updateTrip ---

it('should update a trip and return 202', function () {
    $this->tripService
        ->shouldReceive('updateTrip')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->putJson('/api/trips/1', [
            'destination' => 'Argentina',
        ]);

    $response->assertStatus(202)
             ->assertJson(['message' => 'Trip updated successfully.']);
});

it('should return 422 when updating trip with invalid date format', function () {
    $response = $this->actingAs($this->user, 'api')
        ->putJson('/api/trips/1', [
            'start_date' => 'not-a-valid-date',
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['start_date']);
});

it('should return 404 when updating a non-existent trip', function () {
    $this->tripService
        ->shouldReceive('updateTrip')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found.', Response::HTTP_NOT_FOUND));

    $response = $this->actingAs($this->user, 'api')
        ->putJson('/api/trips/99', ['destination' => 'Argentina']);

    $response->assertStatus(404);
});

it('should return 400 when end_date is before trip start_date on update', function () {
    $this->tripService
        ->shouldReceive('updateTrip')
        ->once()
        ->andThrow(new InvalidDateTripUpdateException(
            "End date can't be before trip's start date.",
            Response::HTTP_BAD_REQUEST
        ));

    $response = $this->actingAs($this->user, 'api')
        ->putJson('/api/trips/1', ['end_date' => '2020-01-01']);

    $response->assertStatus(400);
});

it('should return 401 when updating trip without authentication', function () {
    $response = $this->putJson('/api/trips/1', ['destination' => 'Argentina']);

    $response->assertStatus(401);
});

// --- deleteTrip ---

it('should delete a trip and return 204', function () {
    $this->tripService
        ->shouldReceive('deleteTrip')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson('/api/trips/1');

    $response->assertStatus(204);
});

it('should return 404 when deleting a non-existent trip', function () {
    $this->tripService
        ->shouldReceive('deleteTrip')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found.', Response::HTTP_NOT_FOUND));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson('/api/trips/99');

    $response->assertStatus(404);
});

it('should return 401 when deleting trip without authentication', function () {
    $response = $this->deleteJson('/api/trips/1');

    $response->assertStatus(401);
});
