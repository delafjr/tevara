<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MahasiswaTaskTimelineTest extends TestCase
{
    private function studentSession(): array
    {
        return [
            'api_token' => 'dummy-student-token',
            'user'      => [
                'id'    => 42,
                'name'  => 'Alvian Reza',
                'roles' => ['mahasiswa'],
            ],
            'active_role' => 'mahasiswa',
        ];
    }

    private function dashboardPayload(): array
    {
        return [
            'success' => true,
            'message' => 'Mahasiswa dashboard',
            'data'    => [
                'projects' => [
                    [
                        'id'            => 10,
                        'proposal_code' => 'PRSL-2026-001',
                        'title'         => 'Sistem Informasi Inventaris Digital',
                        'client_name'   => 'PT Sumber Makmur',
                        'role'          => 'Frontend Developer',
                        'status'        => 'Active',
                        'progress'      => 65,
                        'deadline'      => '2026-11-30',
                        'category'      => 'Web Application',
                    ],
                    [
                        'id'            => 20,
                        'proposal_code' => 'PRSL-2026-002',
                        'title'         => 'Aplikasi Mobile Smart Farming',
                        'client_name'   => 'Dinas Pertanian',
                        'role'          => 'Mobile Developer',
                        'status'        => 'Active',
                        'progress'      => 30,
                        'deadline'      => '2026-12-15',
                        'category'      => 'Mobile Application',
                    ],
                ],
            ],
        ];
    }

    private function tasksPayload(): array
    {
        return [
            'success' => true,
            'message' => 'Daftar task proyek',
            'data'    => [
                [
                    'id'                    => 101,
                    'proposal_id'           => 10,
                    'title'                 => 'Slicing Halaman Dashboard Mahasiswa',
                    'status'                => 'TODO',
                    'priority'              => 'HIGH',
                    'progress_percentage'   => 0,
                    'pm_status'             => 'PENDING',
                    'pm_notes'              => null,
                    'due_date'              => '2026-11-20',
                    'achieved_summary'      => null,
                    'blockers_summary'      => null,
                    'report_attachment_url' => null,
                    'assignee'              => [
                        'id'   => 42,
                        'name' => 'Alvian Reza',
                        'nim'  => '4.33.24.1.02',
                    ],
                ],
                [
                    'id'                    => 102,
                    'proposal_id'           => 10,
                    'title'                 => 'Integrasi API Task & Timeline Kanban',
                    'status'                => 'IN_PROGRESS',
                    'priority'              => 'CRITICAL',
                    'progress_percentage'   => 60,
                    'pm_status'             => 'NEEDS_IMPROVEMENT',
                    'pm_notes'              => 'Perbaiki penanganan error boundary saat backend offline.',
                    'due_date'              => '2026-11-25',
                    'achieved_summary'      => 'Komponen kanban sudah terintegrasi dengan store.',
                    'blockers_summary'      => 'Perlu konfirmasi skema UAT.',
                    'report_attachment_url' => 'https://github.com/polines-tefa/sim-tefa-2025/pull/42',
                    'assignee'              => [
                        'id'   => 42,
                        'name' => 'Alvian Reza',
                        'nim'  => '4.33.24.1.02',
                    ],
                ],
                [
                    'id'                    => 103,
                    'proposal_id'           => 10,
                    'title'                 => 'Testing Unit & E2E Skenario Progres',
                    'status'                => 'TESTING',
                    'priority'              => 'MEDIUM',
                    'progress_percentage'   => 100,
                    'pm_status'             => 'PENDING',
                    'pm_notes'              => null,
                    'due_date'              => '2026-11-28',
                    'achieved_summary'      => 'Seluruh 48 test assertions lulus 100%.',
                    'blockers_summary'      => null,
                    'report_attachment_url' => 'https://github.com/polines-tefa/sim-tefa-2025/pull/43',
                    'assignee'              => [
                        'id'   => 42,
                        'name' => 'Alvian Reza',
                        'nim'  => '4.33.24.1.02',
                    ],
                ],
                [
                    'id'                    => 104,
                    'proposal_id'           => 10,
                    'title'                 => 'Inisialisasi Project Repo & Boilerplate',
                    'status'                => 'DONE',
                    'priority'              => 'LOW',
                    'progress_percentage'   => 100,
                    'pm_status'             => 'APPROVED',
                    'pm_notes'              => 'Struktur clean dan sesuai arsitektur.',
                    'due_date'              => '2026-11-10',
                    'achieved_summary'      => 'Boilerplate Tailwind dan AlpineJS disiapkan.',
                    'blockers_summary'      => null,
                    'report_attachment_url' => 'https://github.com/polines-tefa/sim-tefa-2025/pull/1',
                    'assignee'              => [
                        'id'   => 42,
                        'name' => 'Alvian Reza',
                        'nim'  => '4.33.24.1.02',
                    ],
                ],
            ],
        ];
    }

    public function test_halaman_task_mahasiswa_menampilkan_kanban_dan_task_miliknya(): void
    {
        Http::fake([
            '*/api/v1/mahasiswa/dashboard' => Http::response($this->dashboardPayload(), 200),
            '*/api/v1/proposals/10/tasks'  => Http::response($this->tasksPayload(), 200),
            '*/api/v1/proposals/10/tasks/summary' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $response = $this->withSession($this->studentSession())->get('/mahasiswa/task');

        $response->assertOk();
        $response->assertSee('Timeline Saya');
        $response->assertSee('Task &amp;', false);
        $response->assertSee('Sistem Informasi Inventaris Digital');
        $response->assertSee('PRSL-2026-001');
        $response->assertSee('Peran: Frontend Developer');

        // Kanban cards
        $response->assertSee('Slicing Halaman Dashboard Mahasiswa');
        $response->assertSee('Integrasi API Task &amp; Timeline Kanban', false);
        $response->assertSee('Testing Unit &amp; E2E Skenario Progres', false);
        $response->assertSee('Inisialisasi Project Repo &amp; Boilerplate', false);

        // Feedback PM
        $response->assertSee('PM: Perlu Perbaikan');
        $response->assertSee('Perbaiki penanganan error boundary saat backend offline.');
    }

    public function test_halaman_task_mahasiswa_bisa_ganti_proyek_via_query_param(): void
    {
        Http::fake([
            '*/api/v1/mahasiswa/dashboard' => Http::response($this->dashboardPayload(), 200),
            '*/api/v1/proposals/20/tasks'  => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id'                  => 201,
                        'proposal_id'         => 20,
                        'title'               => 'Setup Flutter Base Architecture',
                        'status'              => 'IN_PROGRESS',
                        'priority'            => 'HIGH',
                        'progress_percentage' => 45,
                        'pm_status'           => 'PENDING',
                        'assignee'            => ['name' => 'Alvian Reza'],
                    ],
                ],
            ], 200),
            '*/api/v1/proposals/20/tasks/summary' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $response = $this->withSession($this->studentSession())->get('/mahasiswa/task?proposal_id=20');

        $response->assertOk();
        $response->assertSee('Aplikasi Mobile Smart Farming');
        $response->assertSee('PRSL-2026-002');
        $response->assertSee('Setup Flutter Base Architecture');
    }

    public function test_mahasiswa_dapat_mengirim_laporan_progres_ke_backend(): void
    {
        Http::fake([
            '*/api/v1/proposals/10/tasks/102/progress' => Http::response([
                'success' => true,
                'message' => 'Laporan progres berhasil disimpan.',
                'data'    => [
                    'id'                  => 102,
                    'status'              => 'TESTING',
                    'progress_percentage' => 100,
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->studentSession())
            ->from('/mahasiswa/task')
            ->post('/mahasiswa/proposals/10/tasks/102/progress', [
                'progress_percentage'   => 100,
                'status'                => 'TESTING',
                'achieved_summary'      => 'Semua modul payment gateway dan webhook telah lulus uji.',
                'blockers_summary'      => 'Tidak ada blocker.',
                'report_attachment_url' => 'https://github.com/polines-tefa/sim-tefa-2025/pull/42',
            ]);

        $response->assertRedirect('/mahasiswa/task');
        $response->assertSessionHas('success', 'Laporan progres berhasil dikirim dan menunggu review PM.');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/proposals/10/tasks/102/progress')
                && (int) $request['progress_percentage'] === 100
                && $request['status'] === 'TESTING'
                && $request['report_attachment_url'] === 'https://github.com/polines-tefa/sim-tefa-2025/pull/42';
        });
    }

    public function test_mahasiswa_dapat_mengunggah_berkas_lampiran(): void
    {
        Http::fake([
            '*/api/v1/proposals/10/tasks/102/progress' => Http::response([
                'success' => true,
                'message' => 'Laporan progres berhasil disimpan.',
            ], 200),
        ]);

        $fakeFile = UploadedFile::fake()->create('laporan-testing.pdf', 500, 'application/pdf');

        $response = $this->withSession($this->studentSession())
            ->from('/mahasiswa/task')
            ->post('/mahasiswa/proposals/10/tasks/102/progress', [
                'progress_percentage' => 80,
                'status'              => 'IN_PROGRESS',
                'achieved_summary'    => 'Menyelesaikan modul pengujian dan dokumentasi.',
                'report_attachment'   => $fakeFile,
            ]);

        $response->assertRedirect('/mahasiswa/task');
        $response->assertSessionHas('success', 'Laporan progres berhasil dikirim dan menunggu review PM.');
    }
}
