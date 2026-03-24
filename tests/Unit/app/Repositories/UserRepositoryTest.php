<?php

namespace tests\Unit\app\Repositories;

use App\DTO\Auth\CreateUserDTO;
use App\Repositories\UserRepository;

beforeEach(function () {
    $this->userRepository = new UserRepository();
});

it('should create a user in the database', function () {
    $userDto = CreateUserDTO::process([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'SenhaTeste123'
    ]);

    $this->userRepository->create($userDto);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

it('should find a user in the database by email', function () {
    $userDto = CreateUserDTO::process([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'SenhaTeste123'
    ]);

    $this->userRepository->create($userDto);

    $user = $this->userRepository->findUserByEmail($userDto->email);

    expect($user->email)->toBe('john@example.com');
    expect($user->name)->toBe('John Doe');
});

it('should return null when user is not found by email', function () {
    $user = $this->userRepository->findUserByEmail('naoexiste@example.com');

    expect($user)->toBeNull();
});