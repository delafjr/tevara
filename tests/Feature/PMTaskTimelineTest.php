<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PMTaskTimelineTest extends TestCase
{
    private function sessionData(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user'      => [
                'id'    => 23,
                'name'  => 'Budi Santoso',
                'roles' => ['dosen', 'pm'],
            ],
            'active_role' => 'pm',
        ];
    }

    private function overviewPayload(int $proposalId = 1, string $title = 'Sistem Informasi Manajemen Teaching Factory'): array
    {
        return [
            'success' => true,
            'message' => 'Data monitoring task proyek',
            'data'    => [
                'proposal' => [
                    'id'                     => $proposalId,
                    'proposal_code'          => 'PRSL-2025-001',
                    'title'                  => $title,
                    'status'                 => 'Completed',
                    'category'               => 'Web',
                    'start_date'             => '2025-03-25',
                    'target_completion_date' => '2025-12-15',
                    'pm_id'                  => 23,
                ],
                'summary' => [
                    'total'             => 12,
                    'todo'              => 0,
                    'in_progress'       => 0,
                    'testing'           => 0,
                    'done'              => 12,
                    'pending_review'    => 0,
                    'approved'          => 12,
                    'needs_improvement' => 0,
                    'overall_progress'  => 100,
                ],
                'tasks' => [
                    [
                        'id'                  => 1,
                        'title'               => 'Setup project Laravel & Vue.js',
                        'status'              => 'DONE',
                        'progress_percentage' => 100,
                        'pm_status'           => 'APPROVED',
                        'pm_notes'            => 'Pekerjaan sesuai spesifikasi.',
                        'assignee'            => [
                            'id'            => 34,
                            'name'          => 'Dela Fajar Mulia',
                            'nim'           => '4.33.25.2.07',
                            'study_program' => 'D3 Teknik Informatika',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_halaman_task_timeline_mengambil_data_dari_endpoint_overview(): void
    {
        Http::fake([
            '*/api/v1/dosen/dashboard' => Http::response([
                'success' => true,
                'data'    => [
                    'projects' => [
                        [
                            'id'          => 1,
                            'title'       => 'Sistem Informasi Manajemen Teaching Factory',
                            'client_name' => 'PT. ABC Indonesia',
                            'status'      => 'Completed',
                            'progress'    => 100,
                            'deadline'    => '2025-12-15',
                        ],
                    ],
                ],
            ], 200),
            '*/api/v1/proposals/1/tasks/overview' => Http::response($this->overviewPayload(), 200),
        ]);

        $response = $this->withSession($this->sessionData())->get('/pm/task-timeline');

        $response->assertOk();
        $response->assertSee('PRSL-2025-001');
        $response->assertSee('Sistem Informasi Manajemen Teaching Factory');
        $response->assertSee('Setup project Laravel &amp; Vue.js', false);
        $response->assertSee('Dela Fajar Mulia');
        $response->assertSee('4.33.25.2.07');
        $response->assertSee('Pekerjaan sesuai spesifikasi.');
        $response->assertSee('100%');

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && str_ends_with($request->url(), '/api/v1/proposals/1/tasks/overview');
        });
    }

    public function test_halaman_task_timeline_memilih_proposal_dari_query_string(): void
    {
        Http::fake([
            '*/api/v1/dosen/dashboard' => Http::response([
                'success' => true,
                'data'    => [
                    'projects' => [
                        ['id' => 1, 'title' => 'Proyek Pertama', 'status' => 'Active', 'progress' => 40],
                        ['id' => 2, 'title' => 'Proyek Kedua', 'status' => 'Testing', 'progress' => 80],
                    ],
                ],
            ], 200),
            '*/api/v1/proposals/2/tasks/overview' => Http::response($this->overviewPayload(2, 'Proyek Kedua'), 200),
        ]);

        $response = $this->withSession($this->sessionData())
            ->get('/pm/task-timeline?proposal_id=2');

        $response->assertOk();
        $response->assertSee('Proyek Kedua');

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/api/v1/proposals/2/tasks/overview');
        });
    }

    public function test_halaman_task_timeline_menampilkan_empty_state_tanpa_proyek(): void
    {
        Http::fake([
            '*/api/v1/dosen/dashboard' => Http::response([
                'success' => true,
                'data'    => ['projects' => []],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionData())->get('/pm/task-timeline');

        $response->assertOk();
        $response->assertSee('Belum ada proyek terdaftar');
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/tasks/overview');
        });
    }

    public function test_review_task_dikirim_ke_endpoint_patch_backend(): void
    {
        Http::fake([
            '*/api/v1/proposals/1/tasks/5/review' => Http::response([
                'success' => true,
                'message' => 'Review task berhasil disimpan',
                'data'    => ['id' => 5],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionData())
            ->post('/pm/proposals/1/tasks/5/review', [
                'decision' => 'approve',
                'note'     => 'Sudah sesuai standar industri.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Task berhasil disetujui.');

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_ends_with($request->url(), '/api/v1/proposals/1/tasks/5/review')
                && $request['pm_status'] === 'APPROVED'
                && $request['pm_notes'] === 'Sudah sesuai standar industri.';
        });
    }

    public function test_tambah_task_dikirim_ke_endpoint_store_backend(): void
    {
        Http::fake([
            '*/api/v1/proposals/1/tasks' => Http::response([
                'success' => true,
                'message' => 'Task berhasil dibuat',
                'data'    => ['id' => 9],
            ], 201),
        ]);

        $response = $this->withSession($this->sessionData())
            ->post('/pm/proposals/1/tasks', [
                'title'    => 'Integrasi API pembayaran',
                'priority' => 'HIGH',
                'due_date' => '2026-11-20',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Task baru berhasil ditambahkan.');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/api/v1/proposals/1/tasks')
                && $request['title'] === 'Integrasi API pembayaran'
                && $request['priority'] === 'HIGH';
        });
    }
}
