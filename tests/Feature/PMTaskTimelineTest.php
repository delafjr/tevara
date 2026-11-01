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

    public function test_label_status_membedakan_belum_submit_menunggu_review_dan_laporan_disetujui(): void
    {
        Http::fake([
            '*/api/v1/dosen/dashboard' => Http::response([
                'success' => true,
                'data'    => [
                    'projects' => [
                        ['id' => 1, 'title' => 'Proyek Monitoring', 'status' => 'Active', 'progress' => 56],
                    ],
                ],
            ], 200),
            '*/api/v1/proposals/1/tasks/overview' => Http::response([
                'success' => true,
                'data'    => [
                    'proposal' => [
                        'id'                     => 1,
                        'proposal_code'          => 'PRSL-2026-007',
                        'title'                  => 'Proyek Monitoring',
                        'status'                 => 'Active',
                        'category'               => 'Web',
                        'start_date'             => '2026-04-01',
                        'target_completion_date' => '2026-12-31',
                        'pm_id'                  => 23,
                    ],
                    // Backend menghitung semua PENDING (termasuk task baru) = 2.
                    // FE harus menghitung hanya laporan yang sudah submit = 1.
                    'summary' => [
                        'total'             => 4,
                        'todo'              => 1,
                        'in_progress'       => 2,
                        'testing'           => 0,
                        'done'              => 1,
                        'pending_review'    => 2,
                        'approved'          => 2,
                        'needs_improvement' => 0,
                        'overall_progress'  => 56,
                    ],
                    'tasks' => [
                        [
                            'id'                  => 1,
                            'title'               => 'Task baru belum submit',
                            'status'              => 'TODO',
                            'progress_percentage' => 0,
                            'pm_status'           => 'PENDING',
                            'achieved_summary'    => null,
                            'pm_notes'            => null,
                            'assignee'            => ['id' => 34, 'name' => 'Dela Fajar Mulia', 'nim' => '4.33.25.2.07'],
                        ],
                        [
                            'id'                  => 2,
                            'title'               => 'Laporan progres menunggu review',
                            'status'              => 'IN_PROGRESS',
                            'progress_percentage' => 55,
                            'pm_status'           => 'PENDING',
                            'achieved_summary'    => 'WebSocket sudah berjalan.',
                            'pm_notes'            => null,
                            'assignee'            => ['id' => 35, 'name' => 'Raka Pratama', 'nim' => '4.33.25.2.11'],
                        ],
                        [
                            'id'                  => 3,
                            'title'               => 'Laporan disetujui saat masih berjalan',
                            'status'              => 'IN_PROGRESS',
                            'progress_percentage' => 70,
                            'pm_status'           => 'APPROVED',
                            'achieved_summary'    => 'Subscriber MQTT berjalan.',
                            'pm_notes'            => 'Lanjutkan integrasi multi-sensor.',
                            'assignee'            => ['id' => 36, 'name' => 'Nadia Putri', 'nim' => '4.33.25.2.19'],
                        ],
                        [
                            'id'                  => 4,
                            'title'               => 'Task selesai',
                            'status'              => 'DONE',
                            'progress_percentage' => 100,
                            'pm_status'           => 'APPROVED',
                            'achieved_summary'    => 'Selesai dan sudah diuji.',
                            'pm_notes'            => 'Disetujui.',
                            'assignee'            => ['id' => 37, 'name' => 'Nazifa Citra Nurviani', 'nim' => '4.33.25.2.23'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionData())->get('/pm/task-timeline');

        $response->assertOk();

        // Task baru tanpa laporan tidak boleh tampil sebagai "Menunggu Review".
        $response->assertSee('Belum Submit');
        $response->assertSee('Menunggu Review');

        // Approval pada task yang masih berjalan adalah approval laporan, bukan task selesai.
        $response->assertSee('Laporan Disetujui');

        // Statistik dihitung dari laporan yang benar-benar sudah submit, bukan dari summary mentah backend.
        $response->assertSee('data-stat="pending-review">1</p>', false);
        $response->assertDontSee('data-stat="pending-review">2</p>', false);

        // Done dan approved tidak lagi dicampur dalam satu angka.
        $response->assertSee('data-stat="done">1</p>', false);
        $response->assertSee('2 laporan disetujui');
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
