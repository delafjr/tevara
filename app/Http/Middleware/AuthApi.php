<?php

namespace App\Http\Middleware;

use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;

class AuthApi
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        if (!session('api_token') || !session('user')) {
            return redirect()->route('login');
        }

        if ($role && !ApiService::hasRole(session('user'), $role)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses untuk role ' . $role);
        }

        return $next($request);
    }
}
