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

// --- update ---

it('should update user name in the database', function () {
    $user = \App\Models\User::factory()->create(['name' => 'Old Name']);

    $dto = \App\DTO\User\UpdateUserDto::from(['name' => 'New Name']);

    $this->userRepository->update($dto, $user->id);

    $this->assertDatabaseHas('users', [
        'id'   => $user->id,
        'name' => 'New Name',
    ]);
});

it('should update user password in the database', function () {
    $user = \App\Models\User::factory()->create();

    $hashed = \Illuminate\Support\Facades\Hash::make('newpassword123');
    $dto    = \App\DTO\User\UpdateUserDto::from(['password' => $hashed]);

    $this->userRepository->update($dto, $user->id);

    $updated = \App\Models\User::find($user->id);

    expect(\Illuminate\Support\Facades\Hash::check('newpassword123', $updated->password))->toBeTrue();
});