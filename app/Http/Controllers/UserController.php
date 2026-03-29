<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ){}

    public function register(SignupRequest $request): JsonResponse
    {
        $this->userService->createUser($request->all());

        return response()->json([
            'message' => 'User created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function signin(SigninRequest $request): JsonResponse
    {
        $credentials = $request->all(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Authentication failed.',
                'error' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        $userId = $this->getUserId();

        $userData = $this->userService->findUser($userId);

        return response()->json($userData, Response::HTTP_OK);
    }

    public function logout(): HttpResponse
    {
        auth('api')->logout();

        return response()->noContent();
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $userId = $this->getUserId();

        $this->userService->updateUser($request->all(), $userId);

        return response()->json([
            'message' => 'User data updated successfully.'
        ], Response::HTTP_ACCEPTED);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], Response::HTTP_OK);
    }
}