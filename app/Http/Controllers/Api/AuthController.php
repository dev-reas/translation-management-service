<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterAction;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function register(Request $request, RegisterAction $action): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = $action->execute($request->only(['name', 'email', 'password']));
        $token = $user->createToken('auth-token')->plainTextToken;

        return ResponseService::created([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 'User registered successfully');
    }

    public function login(Request $request, LoginAction $action): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $token = $action->execute($request->only(['email', 'password']));

        if (!$token) {
            return ResponseService::unauthorized('Invalid credentials');
        }

        $user = Auth::user();

        return ResponseService::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseService::success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return ResponseService::success([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);
    }
}