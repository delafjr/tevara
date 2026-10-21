<?php

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard if authenticated, else to login
Route::get('/', function () {
    if (session('api_token') && session('user')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        if (session('api_token') && session('user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request, ApiService $api) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = $api->login($request->email, $request->password);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');
                $user = $data['user'];
                $allowedRoles = ApiService::getUserRoles($user);

                if (empty($allowedRoles)) {
                    return back()->withErrors(['email' => 'Akun tidak memiliki hak akses role.'])->withInput();
                }

                session([
                    'api_token'   => $data['token'],
                    'user'        => $user,
                    'active_role' => $allowedRoles[0],
                ]);

                return redirect()->route('dashboard');
            }

            $message = $response->json('message') ?? 'Email atau password salah.';
            return back()->withErrors(['email' => $message])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Tidak dapat terhubung ke server backend: ' . $e->getMessage()])->withInput();
        }
    });

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', function (Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);
        return redirect('/dashboard');
    });

    Route::post('/logout', function () {
        session()->forget(['api_token', 'user', 'active_role']);
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// Protected routes (require valid session api_token and user)
Route::middleware('auth.api')->group(function () {

    // Main dashboard router
    Route::get('/dashboard', function () {
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (empty($allowedRoles)) {
            abort(403, 'Akun Anda tidak memiliki hak akses role.');
        }

        $activeRole = session('active_role');
        if (!$activeRole || !in_array($activeRole, $allowedRoles, true)) {
            $activeRole = $allowedRoles[0];
            session(['active_role' => $activeRole]);
        }

        $viewMap = [
            'klien'         => 'dashboard',
            'pengurus_tefa' => 'dashboard-pengurus',
            'pm'            => 'dashboard-pm',
            'dosen'         => 'dashboard-pm',
            'mahasiswa'     => 'dashboard-mahasiswa',
            'jurusan'       => 'dashboard-jurusan',
        ];

        $view = $viewMap[$activeRole] ?? 'dashboard';
        return view($view, ['user' => $user, 'currentRole' => $activeRole]);
    })->name('dashboard');

    // Switch Role handlers (Strictly validated against allowed roles)
    Route::get('/switch-role/{role}', function ($role) {
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role.get');

    Route::post('/switch-role', function (Request $request) {
        $role = $request->input('role');
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role');

    // Role-specific dashboard direct URLs
    Route::get('/dashboard-pengurus', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'pengurus_tefa')) {
            abort(403, 'Akses ditolak: Anda bukan Pengurus TeFa.');
        }
        session(['active_role' => 'pengurus_tefa']);
        return redirect()->route('dashboard');
    })->name('dashboard-pengurus');

    Route::get('/dashboard-pm', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'pm')) {
            abort(403, 'Akses ditolak: Anda bukan Project Manager (PM).');
        }
        session(['active_role' => 'pm']);
        return redirect()->route('dashboard');
    })->name('dashboard-pm');

    Route::get('/dashboard-mahasiswa', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'mahasiswa')) {
            abort(403, 'Akses ditolak: Anda bukan Mahasiswa.');
        }
        session(['active_role' => 'mahasiswa']);
        return redirect()->route('dashboard');
    })->name('dashboard-mahasiswa');

    Route::get('/mahasiswa/project', fn () => view('mahasiswa.project', ['user' => session('user')]))->name('mahasiswa.project');
    Route::get('/mahasiswa/task', fn () => view('mahasiswa.task', ['user' => session('user')]))->name('mahasiswa.task');

    Route::get('/dashboard-jurusan', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'jurusan')) {
            abort(403, 'Akses ditolak: Anda bukan Pihak Jurusan.');
        }
        session(['active_role' => 'jurusan']);
        return redirect()->route('dashboard');
    })->name('dashboard-jurusan');

    // Pengurus TEFA sub-pages (Protected by role pengurus_tefa)
    Route::middleware('auth.api:pengurus_tefa')->prefix('pengurus')->name('pengurus.')->group(function () {
        Route::get('/projek', fn () => view('pengurus.projek', ['user' => session('user')]))->name('projek');
        Route::get('/klien-mitra', fn () => view('pengurus.klien-mitra', ['user' => session('user')]))->name('klien-mitra');
        Route::get('/dosen', fn () => view('pengurus.dosen', ['user' => session('user')]))->name('dosen');
        Route::get('/mahasiswa', fn () => view('pengurus.mahasiswa', ['user' => session('user')]))->name('mahasiswa');
        Route::get('/validasi-proposal', fn () => view('pengurus.validasi-proposal', ['user' => session('user')]))->name('validasi-proposal');
        Route::get('/keuangan', fn () => view('pengurus.keuangan', ['user' => session('user')]))->name('keuangan');
        Route::get('/monitoring', fn () => view('pengurus.monitoring', ['user' => session('user')]))->name('monitoring');
    });
});
