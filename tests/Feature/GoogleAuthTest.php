<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    public function test_halaman_login_memuat_google_sdk_dan_tombol_google(): void
    {
        $response = $this->get('/auth/login');

        $response->assertOk();
        $response->assertSee('accounts.google.com/gsi/client');
        $response->assertSee('googleBtn');
        $response->assertSee('handleGoogleCredentialResponse');
        $response->assertSee('/api/v1/auth/google');
    }

    public function test_endpoint_google_auth_berhasil_menerima_id_token_dan_mengeset_session(): void
    {
        Http::fake([
            '*/api/v1/auth/google' => Http::response([
                'success' => true,
                'message' => 'Login Google berhasil',
                'data'    => [
                    'user'  => [
                        'id'    => 10,
                        'name'  => 'Mahasiswa Google',
                        'email' => 'mahasiswa@polines.ac.id',
                        'roles' => ['mahasiswa'],
                    ],
                    'token' => 'mock-sanctum-token-12345',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'dummy-google-jwt-token',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Login Google berhasil',
            'data'    => [
                'token' => 'mock-sanctum-token-12345',
                'user'  => [
                    'email' => 'mahasiswa@polines.ac.id',
                ],
            ],
        ]);
        $response->assertCookie('api_token', 'mock-sanctum-token-12345', false);

        $this->assertEquals('mock-sanctum-token-12345', session('api_token'));
        $this->assertEquals('mahasiswa', session('active_role'));

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/api/v1/auth/google')
                && ($request['id_token'] ?? null) === 'dummy-google-jwt-token';
        });
    }

    public function test_endpoint_auth_google_alias_juga_bekerja(): void
    {
        Http::fake([
            '*/api/v1/auth/google' => Http::response([
                'success' => true,
                'message' => 'Login Google berhasil',
                'data'    => [
                    'user'  => [
                        'id'    => 11,
                        'name'  => 'Klien Google',
                        'email' => 'klien@company.com',
                        'roles' => ['klien'],
                    ],
                    'token' => 'klien-sanctum-token-67890',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/auth/google', [
            'id_token' => 'dummy-klien-token',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data'    => [
                'token' => 'klien-sanctum-token-67890',
            ],
        ]);
        $this->assertEquals('klien-sanctum-token-67890', session('api_token'));
        $this->assertEquals('klien', session('active_role'));
    }

    public function test_endpoint_google_auth_menolak_request_tanpa_id_token(): void
    {
        $response = $this->postJson('/api/v1/auth/google', []);

        $response->assertStatus(422);
    }

    public function test_endpoint_google_auth_meneruskan_error_dari_backend(): void
    {
        Http::fake([
            '*/api/v1/auth/google' => Http::response([
                'success' => false,
                'message' => 'Token Google tidak valid atau sudah kadaluarsa.',
            ], 422),
        ]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'invalid-token',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Token Google tidak valid atau sudah kadaluarsa.',
        ]);
    }

    public function test_cookie_api_token_dapat_memulihkan_session_pada_protected_route(): void
    {
        Http::fake([
            '*/api/v1/auth/me' => Http::response([
                'success' => true,
                'data'    => [
                    'id'    => 15,
                    'name'  => 'User Cookie',
                    'email' => 'cookie@polines.ac.id',
                    'roles' => ['klien'],
                ],
            ], 200),
            '*/api/v1/klien/dashboard' => Http::response([
                'data' => [],
            ], 200),
        ]);

        // Request tanpa session, tapi membawa cookie api_token
        $response = $this->withUnencryptedCookie('api_token', 'token-dari-cookie')
            ->get('/dashboard');

        $response->assertOk();
        $this->assertEquals('token-dari-cookie', session('api_token'));
        $this->assertEquals('klien', session('active_role'));
    }
}
