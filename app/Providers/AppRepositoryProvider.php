<?php

namespace App\Providers;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\Contracts\Repositories\TripRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\TripParticipantsRepository;
use App\Repositories\TripRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppRepositoryProvider extends ServiceProvider
{
    public $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        TripRepositoryInterface::class => TripRepository::class,
        TripParticipantsRepositoryInterface::class => TripParticipantsRepository::class
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
