<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    public function test_guest_can_access_main_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Tevara');
    }

    public function test_authenticated_user_can_access_main_page(): void
    {
        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'roles' => ['klien'],
            ],
            'active_role' => 'klien',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/auth/login');
    }

    public function test_wiktasari_can_access_pengurus_dashboard(): void
    {
        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 5,
                'name' => 'Wiktasari, S.T., M.Kom.',
                'email' => 'wiktasari@polines.ac.id',
                'roles' => ['pengurus_tefa', 'dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'pengurus_tefa',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Wiktasari');
    }

    public function test_wiktasari_cannot_access_pm_dashboard(): void
    {
        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 5,
                'name' => 'Wiktasari, S.T., M.Kom.',
                'roles' => ['pengurus_tefa', 'dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'pengurus_tefa',
        ])->get('/dashboard-pm');

        $response->assertStatus(403);
    }

    public function test_wiktasari_cannot_switch_to_unowned_role(): void
    {
        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 5,
                'name' => 'Wiktasari, S.T., M.Kom.',
                'roles' => ['pengurus_tefa', 'dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'pengurus_tefa',
        ])->get('/switch-role/klien');

        $response->assertStatus(403);
    }

    public function test_pm_can_access_dashboard_pm_when_is_pm_true(): void
    {
        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen PM',
                'roles' => ['dosen'],
                'is_pm' => true,
            ],
            'active_role' => 'pm',
        ])->get('/dashboard-pm');

        $response->assertRedirect('/dashboard');

        $dashResponse = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen PM',
                'roles' => ['dosen'],
                'is_pm' => true,
            ],
            'active_role' => 'pm',
        ])->get('/dashboard');

        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Dosen PM');
    }
}
