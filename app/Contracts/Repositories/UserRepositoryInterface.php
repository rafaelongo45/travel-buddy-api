<?php

namespace App\Contracts\Repositories;

use App\DTO\Auth\CreateUserDTO;
use App\DTO\User\UpdateUserDto;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(CreateUserDTO $userDto): void;

    public function findUserByEmail(string $email): ?User;

    public function findUser(int $userId): ?User;

    public function update(UpdateUserDto $userData, int $userId): void;
}
