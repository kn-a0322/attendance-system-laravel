<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->role === 1) {
            return redirect()->intended('/admin/attendance/list');
        }

        return redirect()->intended('/attendance');
    }
}
