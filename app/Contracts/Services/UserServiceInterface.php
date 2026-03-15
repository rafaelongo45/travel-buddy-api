<?php

namespace App\Contracts\Services;

interface UserServiceInterface
{
    public function createUser(array $userData): void;
}
