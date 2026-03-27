<?php

namespace tests\Feature\Http\Controllers;

use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\TripInviteAlreadySent;
use App\Models\Trip;
use App\Models\User;
use Mockery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

beforeEach(function () {
    $this->tripParticipantsService = Mockery::mock(TripParticipantsServiceInterface::class);
    $this->app->instance(TripParticipantsServiceInterface::class, $this->tripParticipantsService);
    $this->user = User::factory()->create();
});

afterEach(function () {
    Mockery::close();
});

it('should invite a user and return 201', function () {
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('inviteUser')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => $guest->id,
        ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'User invited.']);
});

it('should return 401 when not authenticated', function () {
    $response = $this->postJson('/api/trips/1/invites', [
        'user_id' => 2,
    ]);

    $response->assertStatus(401);
});

it('should return 422 when user_id is missing', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['user_id']);
});

it('should return 422 when user_id does not exist', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => 99999,
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['user_id']);
});

it('should return 422 when trip does not exist', function () {
    $guest = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/trips/99999/invites', [
            'user_id' => $guest->id,
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 422 when trip was soft deleted', function () {
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $this->user->id]);
    $trip->delete();

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => $guest->id,
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 404 when trip does not belong to the authenticated user', function () {
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('inviteUser')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found for the user.', 404));

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => $guest->id,
        ]);

    $response->assertStatus(404);
});

it('should return 400 when user tries to invite himself', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('inviteUser')
        ->once()
        ->andThrow(new BadRequestHttpException("Creator can't invite himself for a trip."));

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => $this->user->id,
        ]);

    $response->assertStatus(400);
});

it('should return invites for a trip and return 200', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('getAllTripInvites')
        ->once()
        ->with($trip->id, $this->user->id)
        ->andReturn(collect([]));

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/trips/{$trip->id}/invites");

    $response->assertStatus(200);
});

it('should return 401 when not authenticated on getInvites', function () {
    $response = $this->getJson('/api/trips/1/invites');

    $response->assertStatus(401);
});

it('should return 404 when trip does not belong to user on getInvites', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('getAllTripInvites')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found for the user.', 404));

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/trips/{$trip->id}/invites");

    $response->assertStatus(404);
});

it('should return 409 when invite was already sent', function () {
    $guest = User::factory()->create();
    $trip  = Trip::factory()->create(['user_id' => $this->user->id]);

    $this->tripParticipantsService
        ->shouldReceive('inviteUser')
        ->once()
        ->andThrow(new TripInviteAlreadySent('The invite was already sent for the user.', 409));

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/trips/{$trip->id}/invites", [
            'user_id' => $guest->id,
        ]);

    $response->assertStatus(409);
});
