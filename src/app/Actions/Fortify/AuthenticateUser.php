<?php

namespace App\Actions\Fortify;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    public function __invoke($request): ?User
    {
        $loginRequest = new LoginRequest();

        Validator::make($request->all(), $loginRequest->rules(), $loginRequest->messages())->validate();

        $user = User::where(Fortify::username(), $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }

        throw ValidationException::withMessages([
            'email' => [$loginRequest->failedAuthenticationMessage()],
        ]);
    }
}
