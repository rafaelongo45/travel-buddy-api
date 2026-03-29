<?php

use App\Http\Controllers\TripController;
use App\Http\Controllers\TripParticipantsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('create', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'signin']);
});


Route::middleware('auth:api')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('me', [UserController::class, 'me']);
    });

    Route::prefix('trips')->group(function () {
        Route::post('/', [TripController::class, 'createTrip']);
        Route::get('/{trip_id}', [TripController::class, 'getUserTrip']);
        Route::get('/', [TripController::class, 'getUserTrips']);
        Route::put('/{trip_id}', [TripController::class, 'updateTrip']);
        Route::delete('/{trip_id}', [TripController::class, 'deleteTrip']);

        Route::post('/{id}/invites', [TripParticipantsController::class, 'invite']);
        Route::get('/{id}/invites', [TripParticipantsController::class, 'getInvites']);
        Route::put('/{id}/invites/{invite_id}', [TripParticipantsController::class, 'updateInviteStatus']);
        Route::delete('/{id}/invites/{invite_id}', [TripParticipantsController::class, 'deleteInvitation']);
    });
});
