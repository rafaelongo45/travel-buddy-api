<?php

namespace App\Contracts\Repositories;

use App\DTO\Auth\CreateUserDTO;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(CreateUserDTO $userDto): void;

    public function findUserByEmail(string $email): ?User;
}
