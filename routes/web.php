<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (session('api_token')) {
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
                    'api_token' => $data['token'],
                    'user'      => $data['user'],
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
        $role = $user['roles'][0] ?? 'klien';
        $map = [
            'klien'         => 'dashboard',
            'pengurus_tefa' => 'dashboard-pengurus',
            'dosen'         => 'dashboard-pm',
            'mahasiswa'     => 'dashboard-mahasiswa',
            'jurusan'       => 'dashboard-jurusan',
        ];
        $view = $map[$role] ?? 'dashboard';
        return view($view, ['user' => $user]);
    })->name('dashboard');
});
