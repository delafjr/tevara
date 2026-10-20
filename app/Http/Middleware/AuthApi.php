<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthApi
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('api_token') || !session('user')) {
            if (app()->environment('local')) {
                session([
                    'api_token'   => 'mock-dev-token',
                    'user'        => [
                        'id'    => 1,
                        'name'  => 'Dimas Setiawan',
                        'email' => 'dimas@polines.ac.id',
                        'roles' => ['pm', 'dosen'],
                    ],
                    'active_role' => session('active_role') ?? 'dosen',
                ]);
                return $next($request);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
