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
