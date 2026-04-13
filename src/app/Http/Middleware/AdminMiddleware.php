<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
       // 1. まずログインしているかチェック
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // 2. ログインしていても、管理者(role=1)でなければ一般画面へ
    if (!Auth::user()->isAdmin()) {
        return redirect()->route('attendance.index');
    }

    // 両方クリア（＝管理者である）なら、管理画面を表示
    return $next($request);
    }
}
