<?php

namespace tests\Feature\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Mockery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

beforeEach(function () {
    $this->userService = Mockery::mock(UserServiceInterface::class);
    $this->app->instance(UserServiceInterface::class, $this->userService);
});

afterEach(function () {
    Mockery::close();
});

// --- register ---

it('should register a user and return 201', function () {
    $this->userService
        ->shouldReceive('createUser')
        ->once();

    $response = $this->postJson('/api/auth/create', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'User created successfully.']);
});

it('should return 422 when required register fields are missing', function () {
    $response = $this->postJson('/api/auth/create', []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('should return 422 when email format is invalid on register', function () {
    $response = $this->postJson('/api/auth/create', [
        'name'     => 'John Doe',
        'email'    => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

it('should return 422 when password is shorter than 8 characters', function () {
    $response = $this->postJson('/api/auth/create', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => '1234567',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['password']);
});

it('should return 422 when email is already taken', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/auth/create', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

it('should propagate exception from service on register', function () {
    $this->userService
        ->shouldReceive('createUser')
        ->once()
        ->andThrow(new BadRequestHttpException('User already exists.'));

    $response = $this->postJson('/api/auth/create', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(400);
});

// --- signin ---

it('should signin and return a token', function () {
    User::factory()->create([
        'email'    => 'john@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
});

it('should return 401 when credentials are wrong', function () {
    $response = $this->postJson('/api/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
             ->assertJson(['error' => 'Unauthorized']);
});
