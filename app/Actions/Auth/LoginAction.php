<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginAction
{
    public function execute(array $credentials): ?string
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return $user->createToken('auth-token')->plainTextToken;
        }

        return null;
    }
}