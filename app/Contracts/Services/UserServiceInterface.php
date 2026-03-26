<?php

namespace App\Contracts\Services;

use App\Models\User;

interface UserServiceInterface
{
    public function createUser(array $userData): void;

    public function findUser(int $userId): User;
}
