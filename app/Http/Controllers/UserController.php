<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController
{
    public function __construct()
    {
        //
    }

    public function register(Request $request)
    {
        $userData = $request->all();
        
        User::create([
            'email' => $userData['email'],
            'name' => $userData['name'],
            'password' => Hash::make($userData['password'])
        ]);

        return response()->json([
            'message' => 'User created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function signin(Request $request)
    {
        if(!Auth::attempt($request->only('email', 'password'))) {
            throw new UnauthorizedHttpException('User data is not valid.');
        }
        
        return response()->json([
            'token' => $request->user()->createToken('signin')->plainTextToken
        ], Response::HTTP_OK);
    }
}