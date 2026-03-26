<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use Illuminate\Http\JsonResponse;

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

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], Response::HTTP_OK);
    }
}