<?php

namespace App\Http\Middleware;

use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;

class AuthApi
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        if ((!session('api_token') || !session('user')) && $request->hasCookie('api_token')) {
            $cookieToken = (string) $request->cookie('api_token');
            try {
                $api = app(ApiService::class);
                $res = $api->withToken($cookieToken)->get($api->getBaseUrl() . '/auth/me');
                if ($res->successful()) {
                    $user = $res->json('data') ?? $res->json('user');
                    if ($user) {
                        $effectiveRoles = ApiService::getEffectiveRoles($user);
                        if (!empty($effectiveRoles)) {
                            session([
                                'api_token'   => $cookieToken,
                                'user'        => $user,
                                'active_role' => $effectiveRoles[0],
                            ]);
                        }
                    }
                }
            } catch (\Throwable) {
                // Biarkan lanjut ke pengecekan session berikutnya.
            }
        }

        if (!session('api_token') || !session('user')) {
            return redirect()->route('login');
        }

        $allowedRoles = $role ? explode('|', $role) : [];
        if ($allowedRoles && !array_intersect($allowedRoles, ApiService::getUserRoles(session('user')))) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses untuk role ' . $role);
        }

        return $next($request);
    }
}
