<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardNotificationTest extends TestCase
{
    public function test_dashboard_renders_notifications_and_unread_count(): void
    {
        Http::fake([
            '*/api/v1/tefa/dashboard' => Http::response([
                'success' => true,
                'data' => [
                    'total_projects' => 5,
                    'running_projects' => 2,
                    'total_clients' => 3,
                    'total_students' => 10,
                    'projects' => [],
                ],
            ], 200),
            '*/api/v1/notifications' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 101,
                        'type' => 'proposal.submitted',
                        'title' => 'Proposal Masuk Baru',
                        'message' => 'Proposal Sistem IoT telah diajukan klien.',
                        'proposal_id' => 99,
                        'is_read' => false,
                        'read_at' => null,
                        'created_at' => now()->toISOString(),
                    ],
                ],
            ], 200),
            '*/api/v1/notifications/unread-count' => Http::response([
                'success' => true,
                'data' => [
                    'unread_count' => 1,
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 1,
                'name' => 'Pengurus TeFa',
                'roles' => ['pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Notifikasi & Aktivitas Terkini', false);
        $response->assertSee('Belum Dibaca', false);
        $response->assertSee('Proposal Masuk Baru', false);
    }

    public function test_mark_as_read_web_route_proxies_to_backend(): void
    {
        Http::fake([
            '*/api/v1/notifications/101/read' => Http::response([
                'success' => true,
                'message' => 'Notifikasi ditandai dibaca',
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 1,
                'name' => 'Pengurus TeFa',
                'roles' => ['pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ])->patchJson('/notifications/101/read');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_mark_all_as_read_web_route_proxies_to_backend(): void
    {
        Http::fake([
            '*/api/v1/notifications/read-all' => Http::response([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 1,
                'name' => 'Pengurus TeFa',
                'roles' => ['pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ])->patchJson('/notifications/read-all');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
