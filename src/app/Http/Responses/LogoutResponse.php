<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // ログアウト POST の URL は常に /logout のため、フォームの hidden で区別する
        if ($request->input('logout_to') === 'admin') {
            return redirect()->route('admin.login');
        }

        return redirect()->route('login');
    }
}
