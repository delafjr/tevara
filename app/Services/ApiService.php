<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Authenticate user with backend API.
     */
    public function login(string $email, string $password): Response
    {
        return Http::timeout(10)->post($this->baseUrl . '/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * Create an HTTP client pending request with Bearer token.
     */
    public function withToken(?string $token = null): PendingRequest
    {
        $token = $token ?? session('api_token');
        return Http::withToken($token)->timeout(15);
    }

    /**
     * Send GET request to backend API.
     */
    public function get(string $endpoint, array $query = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->withToken()->get($url, $query);
    }

    /**
     * Send GET request to a public endpoint (without Bearer token).
     */
    public function getPublic(string $endpoint, array $query = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return Http::timeout(15)->get($url, $query);
    }

    /**
     * Company profile milik user klien yang sedang login.
     * Mengembalikan array kosong bila API gagal agar halaman tetap bisa dirender.
     */
    public function getCompanyProfile(): array
    {
        try {
            $response = $this->get('users/company');
            $data = $response->json('data');

            return $response->successful() && is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Master kategori proyek (public) dengan bentuk [{id, name}, ...].
     */
    public function getCategories(): array
    {
        try {
            $response = $this->getPublic('categories');
            $data = $response->json('data');

            if (!$response->successful() || !is_array($data)) {
                return [];
            }

            return collect($data)
                ->filter(fn ($item) => is_array($item) && isset($item['id'], $item['name']))
                ->map(fn ($item) => [
                    'id'   => (int) $item['id'],
                    'name' => (string) $item['name'],
                ])
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Send POST request to backend API.
     */
    public function post(string $endpoint, array $data = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->withToken()->post($url, $data);
    }

    /**
     * Send PUT request to backend API.
     */
    public function put(string $endpoint, array $data = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->withToken()->put($url, $data);
    }

    /**
     * Send DELETE request to backend API.
     */
    public function delete(string $endpoint, array $data = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->withToken()->delete($url, $data);
    }

    /**
     * Extract allowed roles from user payload.
     * Include PM access when assigned explicitly or indicated by is_pm.
     */
    public static function getUserRoles(?array $user): array
    {
        if (!$user) {
            return [];
        }

        $roles = $user['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!empty($user['is_pm']) && !in_array('pm', $roles, true)) {
            $roles[] = 'pm';
        }

        return array_values(array_unique($roles));
    }

    /**
     * Roles yang dipakai untuk navigasi.
     * Dosen dan PM memakai dashboard yang sama, jadi bila user juga PM,
     * role dosen tidak ditampilkan lagi agar tidak duplikat.
     */
    public static function getEffectiveRoles(?array $user): array
    {
        $roles = self::getUserRoles($user);

        if (in_array('pm', $roles, true)) {
            $roles = array_values(array_diff($roles, ['dosen']));
        }

        return $roles;
    }

    /**
     * Check if user possesses specific role.
     */
    public static function hasRole(?array $user, string $role): bool
    {
        return in_array($role, self::getUserRoles($user), true);
    }
}
