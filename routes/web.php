<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

$backendUrl = fn () => rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');

Route::get('/', function () use ($backendUrl) {
    $base = $backendUrl();
    $initialData = null;
    $initialError = null;

    try {
        // login untuk mendapat token (ponytail: kredensial demo hardcode; ganti session partner saat fitur auth disatukan)
        $login = Http::timeout(30)->post($base.'/auth/login', [
            'email'    => 'owner@majubersamasentosa.co.id',
            'password' => 'password',
        ]);

        if (! $login->successful()) {
            throw new RuntimeException('Login gagal: HTTP '.$login->status());
        }

        $token = $login->json('data.token');

        $response = Http::withToken($token)->timeout(30)->get($base.'/users');
        if ($response->successful()) {
            $initialData = $response->json();
        } else {
            $initialError = 'HTTP '.$response->status().' - '.$response->reason();
        }
    } catch (Throwable $e) {
        $initialError = $e->getMessage();
    }

    return view('welcome', [
        'apiUrl' => $base.'/users',
        'initialData' => $initialData,
        'initialError' => $initialError,
    ]);
});

Route::prefix('auth')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        return redirect('/dashboard');
    });

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        return redirect('/dashboard');
    });
});

Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
Route::get('/dashboard-pengurus', fn () => view('dashboard-pengurus'))->name('dashboard-pengurus');
Route::get('/dashboard-pm', fn () => view('dashboard-pm'))->name('dashboard-pm');
