<?php

use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('create', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'signin']);
});

Route::middleware('auth')->group(function () {
    Route::prefix('trips')->group(function () {
        Route::post('/', [TripController::class, 'createTrip']);
        Route::get('/', [TripController::class, 'getUserTrips']);
    });
});
