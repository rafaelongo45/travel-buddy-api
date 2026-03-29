<?php

namespace App\Contracts\Services;

use App\Models\User;

interface UserServiceInterface
{
    public function createUser(array $userData): void;

    public function findUser(int $userId): User;

    public function updateUser(array $userData, int $userId): void;
}
