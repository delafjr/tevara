<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
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

        Http::fake();

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

    public function test_pm_dashboard_renders_data_from_dosen_dashboard_api(): void
    {
        Http::fake([
            '*/dosen/dashboard' => Http::response([
                'success' => true,
                'message' => 'Dosen dashboard',
                'data' => [
                    'total_projects' => 4,
                    'active_projects' => 2,
                    'completed_projects' => 1,
                    'waiting_review' => 1,
                    'projects' => [
                        [
                            'id' => 1,
                            'title' => 'Sistem Informasi Manajemen Teaching Factory',
                            'client_name' => 'PT. Maju Bersama Sentosa',
                            'status' => 'Completed',
                            'progress' => 100,
                            'deadline' => '2025-12-15',
                        ],
                    ],
                    'category_distribution' => [
                        ['web' => 4],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen PM',
                'roles' => ['dosen'],
                'is_pm' => true,
            ],
            'active_role' => 'pm',
        ])->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Sistem Informasi Manajemen Teaching Factory');
        $response->assertSee('PT. Maju Bersama Sentosa');
        $response->assertSee('Completed');
        $response->assertSee('100%');
    }

    public function test_dosen_without_projects_renders_zero_cards_and_empty_state(): void
    {
        Http::fake([
            '*/dosen/dashboard' => Http::response([
                'success' => true,
                'message' => 'Dosen dashboard',
                'data' => [
                    'total_projects' => 0,
                    'active_projects' => 0,
                    'completed_projects' => 0,
                    'waiting_review' => 0,
                    'projects' => [],
                    'category_distribution' => [],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen Baru',
                'roles' => ['dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ])->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Workspace Dosen');
        $response->assertSee('Anda belum pernah menangani project');
        $response->assertSee('>0</p>', false);
    }

    public function test_pm_can_access_project_and_task_timeline_pages(): void
    {
        $session = [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen PM',
                'roles' => ['dosen'],
                'is_pm' => true,
            ],
            'active_role' => 'pm',
        ];

        $projectResponse = $this->withSession($session)->get('/pm/project');
        $projectResponse->assertOk();
        $projectResponse->assertSee('href="' . route('pm.project') . '"', false);
        $projectResponse->assertSee('href="' . route('pm.task-timeline') . '"', false);

        $taskTimelineResponse = $this->withSession($session)->get('/pm/task-timeline');
        $taskTimelineResponse->assertOk();
        $taskTimelineResponse->assertSee('href="' . route('pm.project') . '"', false);
        $taskTimelineResponse->assertSee('href="' . route('pm.task-timeline') . '"', false);
    }

    public function test_dosen_pm_sidebar_links_open_pm_pages(): void
    {
        $session = [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen PM',
                'roles' => ['dosen', 'pm'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ];

        Http::fake();

        $response = $this->withSession($session)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('href="' . route('pm.project') . '"', false);
        $response->assertSee('href="' . route('pm.anggota-tim') . '"', false);
        $response->assertSee('href="' . route('pm.task-timeline') . '"', false);
        $response->assertSee('Workspace Project Manager');
        $response->assertSee('Anda belum pernah menangani project');

        $this->withSession($session)->get(route('pm.project'))->assertOk();
        $this->withSession($session)->get(route('pm.task-timeline'))->assertOk();
    }

    public function test_sidebar_merges_dosen_role_into_pm_when_user_is_also_pm(): void
    {
        Http::fake();

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Eri Eli Lavindi',
                'roles' => ['dosen'],
                'is_pm' => true,
            ],
            'active_role' => 'dosen',
        ])->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Workspace Project Manager');
        $response->assertSee('<span>Project Manager</span>', false);
        $response->assertDontSee('<span>Dosen</span>', false);
        $response->assertSee('1 Role');
    }

    public function test_dosen_can_open_project_and_task_pages_when_no_project_is_assigned(): void
    {
        Http::fake();

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen',
                'roles' => ['dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ])->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Workspace Dosen');
        $response->assertSee('Anda belum pernah menangani project');
        $response->assertSee('href="' . route('pm.project') . '"', false);
        $response->assertSee('href="' . route('pm.anggota-tim') . '"', false);
        $response->assertSee('href="' . route('pm.task-timeline') . '"', false);

        $projectResponse = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen',
                'roles' => ['dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ])->get(route('pm.project'));

        $projectResponse->assertOk();
        $projectResponse->assertSee('PRJ-2026/044');

        $teamResponse = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen',
                'roles' => ['dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ])->get(route('pm.anggota-tim'));

        $teamResponse->assertOk();
        $teamResponse->assertSee('PRJ-2026/014');

        $taskResponse = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 6,
                'name' => 'Dosen',
                'roles' => ['dosen'],
                'is_pm' => false,
            ],
            'active_role' => 'dosen',
        ])->get(route('pm.task-timeline'));

        $taskResponse->assertOk();
        $taskResponse->assertSee('PRJ-2026/001');
    }
}
