<?php

namespace App\DTO\Auth;

use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class CreateUserDTO extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ){}

    public static function process(array $data): self
    {
        return self::from([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
