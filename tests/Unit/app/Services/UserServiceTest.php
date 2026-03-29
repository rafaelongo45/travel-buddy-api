<?php

namespace tests\Unit\app\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\UserService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->userService = new UserService($this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

it('should create a user successfully', function () {
    $userData = [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ];

    $this->userRepository
        ->shouldReceive('findUserByEmail')
        ->once()
        ->with($userData['email'])
        ->andReturn(null);

    $this->userRepository
        ->shouldReceive('create')
        ->once();

    $this->userService->createUser($userData);

    expect(true)->toBeTrue();
});

it('should throw BadRequestHttpException when user already exists', function () {
    $userData = [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ];

    $this->userRepository
        ->shouldReceive('findUserByEmail')
        ->once()
        ->with($userData['email'])
        ->andReturn(new User());

    $this->userService->createUser($userData);
})->throws(BadRequestHttpException::class);

// --- updateUser ---

it('should update user with name only', function () {
    $this->userRepository
        ->shouldReceive('update')
        ->once();

    $this->userService->updateUser(['name' => 'New Name'], 1);

    expect(true)->toBeTrue();
});

it('should hash password before updating', function () {
    $plainPassword = 'newpassword123';

    $this->userRepository
        ->shouldReceive('update')
        ->once()
        ->withArgs(function ($dto) use ($plainPassword) {
            return $dto->password !== $plainPassword
                && \Illuminate\Support\Facades\Hash::check($plainPassword, $dto->password);
        });

    $this->userService->updateUser(['password' => $plainPassword], 1);

    expect(true)->toBeTrue();
});

it('should not hash password when not provided', function () {
    $this->userRepository
        ->shouldReceive('update')
        ->once()
        ->withArgs(function ($dto) {
            return $dto->password instanceof \Spatie\LaravelData\Optional;
        });

    $this->userService->updateUser(['name' => 'John'], 1);

    expect(true)->toBeTrue();
});
