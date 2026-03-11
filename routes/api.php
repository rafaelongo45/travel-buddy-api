<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('create', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'signin']);
});
