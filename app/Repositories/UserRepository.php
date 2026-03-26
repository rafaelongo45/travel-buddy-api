<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTO\Auth\CreateUserDTO;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function create(CreateUserDTO $userDto): void
    {
        User::create($userDto->toArray());
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where([
            'email' => $email
        ])->first();
    }

    public function findUser(int $userId): ?User
    {
        return User::where('id', $userId)->first();
    }
}
