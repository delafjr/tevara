<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PengurusProjekDetailTest extends TestCase
{
    private function sessionPengurus(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 5,
                'name' => 'Wiktasari, S.T., M.Kom.',
                'email' => 'wiktasari@polines.ac.id',
                'roles' => ['pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ];
    }

    public function test_pengurus_can_access_projek_detail_with_pm_students_progress_and_uat(): void
    {
        Http::fake([
            '*proposals/16' => Http::response([
                'id' => 16,
                'proposal_code' => 'PRSL-2026-016',
                'title' => 'Aplikasi Monitoring IoT Pertanian Berbasis Web',
                'client_company_name' => 'CV. Teknologi Nusantara',
                'client' => [
                    'id' => 10,
                    'name' => 'Budi Santoso',
                    'email' => 'budi@mitra.test',
                    'phone_number' => '08123456789',
                ],
                'pm' => [
                    'id' => 3,
                    'name' => 'Liliek Triyono, S.T., M.Kom.',
                    'email' => 'liliek@polines.ac.id',
                ],
                'project_teams' => [
                    [
                        'id' => 1,
                        'user' => [
                            'id' => 20,
                            'name' => 'Ahmad Dani',
                            'student_profile' => ['nim' => '3.34.21.0.01'],
                        ],
                        'role' => ['name' => 'Frontend Developer'],
                    ],
                    [
                        'id' => 2,
                        'user' => [
                            'id' => 21,
                            'name' => 'Citra Lestari',
                            'student_profile' => ['nim' => '3.34.21.0.02'],
                        ],
                        'role' => ['name' => 'Backend Developer'],
                    ],
                ],
                'overall_progress' => 46,
                'category' => ['id' => 4, 'name' => 'IoT'],
                'status' => ['id' => 8, 'name' => 'Testing'],
                'desired_deadline' => '2026-09-30',
                'estimated_budget' => 35000000,
                'background' => 'Kebutuhan sistem IoT untuk monitoring tanah.',
                'target_audience' => 'Petani dan agronomis.',
                'modules' => [
                    ['title' => 'Modul Sensor', 'description' => 'Pembacaan data realtime'],
                ],
            ], 200),
            '*proposals/16/uat' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 5,
                    'session_date' => '2026-11-18',
                    'overall_result' => 'PASSED',
                    'review_status' => 'APPROVED',
                    'client_signoff_name' => 'Budi Santoso',
                    'signed_off_at' => '2026-11-18T10:00:00Z',
                    'uat_items' => [
                        [
                            'feature_name' => 'Dashboard Sensor',
                            'test_scenario' => 'Melihat grafik',
                            'result' => 'PASSED',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/projek/16');

        $response->assertOk()
            ->assertSee('Aplikasi Monitoring IoT Pertanian Berbasis Web')
            ->assertSee('PRSL-2026-016')
            ->assertSee('Liliek Triyono, S.T., M.Kom.')
            ->assertSee('Ahmad Dani')
            ->assertSee('Frontend Developer')
            ->assertSee('Citra Lestari')
            ->assertSee('Backend Developer')
            ->assertSee('46%')
            ->assertSee('PASSED')
            ->assertSee('Disetujui')
            ->assertSee('Budi Santoso');
    }
}
