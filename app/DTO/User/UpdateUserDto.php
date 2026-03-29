<?php

namespace App\DTO\User;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateUserDto extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string|Optional $name,
        public string|Optional $password
    ){}
}
