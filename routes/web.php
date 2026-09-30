<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $backendUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
    $apiUrl = $backendUrl . '/users';

    $initialData = null;
    $initialError = null;

    try {
        $response = Http::timeout(3)->get($apiUrl);
        if ($response->successful()) {
            $initialData = $response->json();
        } else {
            $initialError = 'HTTP ' . $response->status() . ' - ' . $response->reason();
        }
    } catch (\Throwable $e) {
        $initialError = $e->getMessage();
    }

    return view('welcome', [
        'apiUrl' => $apiUrl,
        'initialData' => $initialData,
        'initialError' => $initialError,
    ]);
});
