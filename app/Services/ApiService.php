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
     * Send PATCH request to backend API.
     */
    public function patch(string $endpoint, array $data = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->withToken()->patch($url, $data);
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
     * Check if user possesses specific role.
     */
    public static function hasRole(?array $user, string $role): bool
    {
        return in_array($role, self::getUserRoles($user), true);
    }
}
