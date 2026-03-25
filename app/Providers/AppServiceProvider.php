<?php

namespace App\Providers;

use App\Contracts\Services\TripServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Services\TripService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        UserServiceInterface::class => UserService::class,
        TripServiceInterface::class => TripService::class
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
