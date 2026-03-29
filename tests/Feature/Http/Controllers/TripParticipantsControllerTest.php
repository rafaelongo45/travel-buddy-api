<?php

namespace tests\Feature\Http\Controllers;

use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Exceptions\CannotInviteYourselfException;
use App\Exceptions\InviteDoesNotBelongToTripException;
use App\Exceptions\InvalidUserTripInvite;
use App\Exceptions\Trip\TripNotFoundException;
use App\Exceptions\TripInvitationNotFound;
use App\Exceptions\TripInviteAlreadySent;
use App\Models\Trip;
use App\Models\TripParticipant;
use App\Models\User;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

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
        ->andThrow(new CannotInviteYourselfException("Creator can't invite himself for a trip.", Response::HTTP_BAD_REQUEST));

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

// updateInviteStatus

it('should update invite status and return 202', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('updateInviteStatus')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(202)
             ->assertJson(['message' => 'Invite updated successfully.']);
});

it('should return 401 when not authenticated on updateInviteStatus', function () {
    $response = $this->putJson('/api/trips/1/invites/1', [
        'status' => 'accepted',
    ]);

    $response->assertStatus(401);
});

it('should return 422 when status is missing', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['status']);
});

it('should return 422 when status is invalid', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'pending',
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['status']);
});

it('should return 422 when trip does not exist on updateInviteStatus', function () {
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/99999/invites/{$invite->id}", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 422 when trip was soft deleted on updateInviteStatus', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);
    $trip->delete();
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 422 when invite does not exist', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/99999", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['invite_id']);
});

it('should return 404 when invite is not found on service', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('updateInviteStatus')
        ->once()
        ->andThrow(new TripInvitationNotFound("Trip invitation doesn't exist.", Response::HTTP_NOT_FOUND));

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(404);
});

it('should return 403 when user is not the invited user', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('updateInviteStatus')
        ->once()
        ->andThrow(new InvalidUserTripInvite("User wasn't invited for the trip.", Response::HTTP_FORBIDDEN));

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'accepted',
        ]);

    $response->assertStatus(403);
});

it('should return 400 when invite is already responded', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
        'status'  => 'accepted',
    ]);

    $this->tripParticipantsService
        ->shouldReceive('updateInviteStatus')
        ->once()
        ->andThrow(new InvalidUserTripInvite("Trip is not pending and can't be accepted/refuse.", Response::HTTP_BAD_REQUEST));

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/trips/{$trip->id}/invites/{$invite->id}", [
            'status' => 'refused',
        ]);

    $response->assertStatus(400);
});

// deleteInvitation

it('should delete an invitation and return 204', function () {
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('deleteInvitation')
        ->once();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(204);
});

it('should return 401 when not authenticated on deleteInvitation', function () {
    $response = $this->deleteJson('/api/trips/1/invites/1');

    $response->assertStatus(401);
});

it('should return 422 when trip does not exist on deleteInvitation', function () {
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/99999/invites/{$invite->id}");

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 422 when trip was soft deleted on deleteInvitation', function () {
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $this->user->id,
        'trip_id' => $trip->id,
    ]);
    $trip->delete();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['trip_id']);
});

it('should return 422 when invite does not exist on deleteInvitation', function () {
    $trip = Trip::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/99999");

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['invite_id']);
});

it('should return 422 when invite was soft deleted on deleteInvitation', function () {
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);
    $invite->delete();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['invite_id']);
});

it('should return 404 when trip does not belong to user on deleteInvitation', function () {
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('deleteInvitation')
        ->once()
        ->andThrow(new TripNotFoundException('Trip was not found for the user.', 404));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(404);
});

it('should return 404 when invite is not found on service on deleteInvitation', function () {
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('deleteInvitation')
        ->once()
        ->andThrow(new TripInvitationNotFound("Trip invitation doesn't exist.", Response::HTTP_NOT_FOUND));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(404);
});

it('should return 403 when invite belongs to a different trip on deleteInvitation', function () {
    $guest  = User::factory()->create();
    $trip   = Trip::factory()->create(['user_id' => $this->user->id]);
    $invite = TripParticipant::factory()->create([
        'user_id' => $guest->id,
        'trip_id' => $trip->id,
    ]);

    $this->tripParticipantsService
        ->shouldReceive('deleteInvitation')
        ->once()
        ->andThrow(new InviteDoesNotBelongToTripException('Trip invitation does not belong to user', Response::HTTP_FORBIDDEN));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/trips/{$trip->id}/invites/{$invite->id}");

    $response->assertStatus(403);
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
