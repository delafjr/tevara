<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthApi
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('api_token') || !session('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
