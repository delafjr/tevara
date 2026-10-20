<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (session('api_token') || app()->environment('local')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        if (session('api_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $backendUrl = rtrim(config('services.backend_api.url'), '/');

        try {
            $response = Http::timeout(5)->post($backendUrl . '/auth/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');
                session([
                    'api_token'   => $data['token'],
                    'user'        => $data['user'],
                    'active_role' => $data['user']['roles'][0] ?? 'klien',
                ]);
                return redirect()->route('dashboard');
            }

            return back()->withErrors(['email' => $response->json('message', 'Login gagal')])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Tidak dapat terhubung ke server: ' . $e->getMessage()])->withInput();
        }
    });

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        return redirect('/dashboard');
    });

    Route::post('/logout', function () {
        session()->forget(['api_token', 'user']);
        return redirect()->route('login');
    })->name('logout');
});

// Dashboard routes — protected by session check
Route::middleware('auth.api')->group(function () {
    Route::get('/dashboard', function () {
        $user = session('user');
        $role = session('active_role') ?? ($user['roles'][0] ?? 'klien');
        if ($role === 'pm') {
            $role = 'dosen';
        }

        $map = [
            'klien'         => 'dashboard',
            'pengurus_tefa' => 'dashboard-pengurus',
            'dosen'         => 'dashboard-pm',
            'mahasiswa'     => 'dashboard-mahasiswa',
            'jurusan'       => 'dashboard-jurusan',
        ];

        $view = $map[$role] ?? 'dashboard';
        return view($view, ['user' => $user, 'currentRole' => $role]);
    })->name('dashboard');

    // Switch Role handlers
    Route::get('/switch-role/{role}', function ($role) {
        $validRoles = ['klien', 'pengurus_tefa', 'dosen', 'pm', 'mahasiswa', 'jurusan'];
        if (in_array($role, $validRoles)) {
            session(['active_role' => $role === 'pm' ? 'dosen' : $role]);
        }
        return redirect()->route('dashboard');
    })->name('switch-role.get');

    Route::post('/switch-role', function (Request $request) {
        $role = $request->input('role');
        $validRoles = ['klien', 'pengurus_tefa', 'dosen', 'pm', 'mahasiswa', 'jurusan'];
        if (in_array($role, $validRoles)) {
            session(['active_role' => $role === 'pm' ? 'dosen' : $role]);
        }
        return redirect()->route('dashboard');
    })->name('switch-role');

    // Direct dashboard aliases
    Route::get('/dashboard-pengurus', function () {
        session(['active_role' => 'pengurus_tefa']);
        return redirect()->route('dashboard');
    })->name('dashboard-pengurus');

    Route::get('/dashboard-pm', function () {
        session(['active_role' => 'dosen']);
        return redirect()->route('dashboard');
    })->name('dashboard-pm');

    Route::get('/dashboard-mahasiswa', function () {
        session(['active_role' => 'mahasiswa']);
        return redirect()->route('dashboard');
    })->name('dashboard-mahasiswa');

    Route::get('/dashboard-jurusan', function () {
        session(['active_role' => 'jurusan']);
        return redirect()->route('dashboard');
    })->name('dashboard-jurusan');

    // ── Pengurus TEFA sub-pages ──
    Route::get('/pengurus/projek', fn () => view('pengurus.projek', ['user' => session('user')]))->name('pengurus.projek');
    Route::get('/pengurus/klien-mitra', fn () => view('pengurus.klien-mitra', ['user' => session('user')]))->name('pengurus.klien-mitra');
    Route::get('/pengurus/dosen', fn () => view('pengurus.dosen', ['user' => session('user')]))->name('pengurus.dosen');
    Route::get('/pengurus/mahasiswa', fn () => view('pengurus.mahasiswa', ['user' => session('user')]))->name('pengurus.mahasiswa');
    Route::get('/pengurus/validasi-proposal', fn () => view('pengurus.validasi-proposal', ['user' => session('user')]))->name('pengurus.validasi-proposal');
    Route::get('/pengurus/keuangan', fn () => view('pengurus.keuangan', ['user' => session('user')]))->name('pengurus.keuangan');
    Route::get('/pengurus/monitoring', fn () => view('pengurus.monitoring', ['user' => session('user')]))->name('pengurus.monitoring');
});
