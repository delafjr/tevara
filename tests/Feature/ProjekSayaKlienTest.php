<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ProjekSayaKlienTest extends TestCase
{
    private function sessionKlien(): array
    {
        return [
            'api_token'   => 'dummy-token',
            'user'        => [
                'id'           => 106,
                'name'         => 'Budi Santoso',
                'email'        => 'budi.santoso@majubersamasentosa.co.id',
                'phone_number' => '6281234567890',
                'company_name' => 'PT. Maju Bersama Sentosa',
                'position'     => 'IT Manager',
                'roles'        => ['klien'],
                'is_pm'        => false,
            ],
            'active_role' => 'klien',
        ];
    }

    public function test_halaman_projek_saya_bisa_diakses_klien(): void
    {
        Http::fake(['*' => Http::response([], 200)]);
        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya');

        $response->assertStatus(200);
        $response->assertSee('Daftar');
        $response->assertSee('Projek Saya');
        $response->assertSee('PRJ-2026/014');
        $response->assertSee('Sistem Monitoring Rantai Pasok Terpadu');
        $response->assertSee('Dr. Hendra Kusuma, M.T.');
        $response->assertSee('PRJ-2026/008');
        $response->assertSee('PRJ-2026/021');
        $response->assertSee('TOTAL PROYEK PORTOFOLIO');
        $response->assertSee('RATA - RATA PROGRES');
        $response->assertSee('DISTRIBUSI FASE PENGERJAAN');
        $response->assertSee('Berita Acara Serah Terima (BAST)');
        $response->assertSee('Tautan repositori');
        $response->assertSee('Masa garansi (bulan)');
        $response->assertSee('UAT terakhir berstatus PASSED');
        $response->assertSee('Proyek siap serah terima');
        $response->assertSee('Belum ada proyek dengan hasil UAT terakhir PASSED.');
        $response->assertSee('Kirim persetujuan');
    }

    public function test_proyek_dengan_uat_passed_muncul_di_daftar_bast(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([[
                'id' => 501,
                'project_code' => 'PRJ-2026/501',
                'title' => 'Proyek UAT Lulus',
                'bast_eligible' => true,
            ]], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya');

        $response->assertOk()
            ->assertSee('PRJ-2026/501')
            ->assertSee('Proyek UAT Lulus')
            ->assertSee('Isi formulir BAST');
    }

    public function test_formulir_bast_dipost_ke_proposal_milik_klien(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([[
                'id' => 501,
                'project_code' => 'PRJ-2026/501',
                'bast_eligible' => true,
            ]], 200),
            '*proposals/501/handover' => Http::response(['success' => true], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->post('/klien/projek-saya/bast', [
                'project_code' => 'PRJ-2026/501',
                'handover_date' => '2026-11-01',
                'repository_url' => 'https://example.test/repository',
                'documentation_url' => 'https://example.test/documentation',
                'user_manual_url' => 'https://example.test/manual',
                'client_signoff_name' => 'Klien Tevara',
                'client_approved' => '1',
                'maintenance_warranty_months' => '3',
                'maintenance_start_date' => '2026-11-01',
                'maintenance_end_date' => '2027-02-01',
            ]);

        $response->assertRedirect('/klien/projek-saya')
            ->assertSessionHas('success', 'BAST berhasil disetujui.');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/proposals/501/handover'));
    }

    public function test_halaman_projek_saya_dicegah_untuk_role_lain(): void
    {
        $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => ['id' => 1, 'name' => 'Mahasiswa User', 'roles' => ['mahasiswa']],
            'active_role' => 'mahasiswa',
        ])->get('/klien/projek-saya')->assertStatus(403);
    }

    public function test_alias_proyek_saya_redirect_ke_projek_saya(): void
    {
        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/proyek-saya');

        $response->assertRedirect('/klien/projek-saya');
    }

    public function test_detail_projek_active_menampilkan_progress_dan_tim_pelaksana(): void
    {
        Http::fake([
            '*/api/v1/proposals/9' => Http::response([
                'id'                  => 9,
                'proposal_code'       => 'PRSL-2026-009',
                'client_company_name' => 'PT. Maju Bersama Sentosa',
                'title'               => 'Sistem Informasi Manajemen Teaching Factory',
                'background'          => 'Pembuatan sistem informasi manajemen teaching factory terintegrasi.',
                'target_audience'     => 'Pengurus TeFa dan mahasiswa',
                'category_id'         => 1,
                'category'            => ['id' => 1, 'name' => 'Web'],
                'status_id'           => 7,
                'status'              => ['id' => 7, 'name' => 'Active'],
                'desired_deadline'    => '2026-12-15',
                'estimated_budget'    => '30000000.00',
                'overall_progress'    => 62,
                'pm'                  => ['id' => 15, 'name' => 'Dr. Hendra Kusuma, M.T.'],
                'project_teams'       => [
                    [
                        'id'    => 1,
                        'user'  => ['id' => 28, 'name' => 'Aang Kunadi'],
                        'role'  => ['id' => 2, 'name' => 'Frontend'],
                    ],
                    [
                        'id'    => 2,
                        'user'  => ['id' => 29, 'name' => 'Alvian Reza Mahardika'],
                        'role'  => ['id' => 3, 'name' => 'Backend'],
                    ],
                ],
                'client' => [
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'phone_number' => '6281234567890',
                ],
                'modules' => [
                    ['id' => 1, 'title' => 'Modul Manajemen Proposal', 'description' => 'Pengelolaan proposal proyek.'],
                ],
            ], 200),
            '*/api/v1/categories' => Http::response(['success' => true, 'data' => [['id' => 1, 'name' => 'Web']]], 200),
            '*/api/v1/users/company' => Http::response([
                'success' => true,
                'data'    => [
                    'company_name' => 'PT. Maju Bersama Sentosa',
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp'        => '6281234567890',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya/9');

        $response->assertOk();
        $response->assertSee('Progres Pengerjaan');
        $response->assertSee('Overall Progress');
        $response->assertSee('62%');
        $response->assertSee('Project Manager');
        $response->assertSee('Dr. Hendra Kusuma, M.T.');
        $response->assertSee('Tim Pelaksana');
        $response->assertSee('Aang Kunadi');
        $response->assertSee('Alvian Reza Mahardika');
        $response->assertSee('Frontend');
        $response->assertSee('Backend');
    }

    public function test_detail_projek_submitted_tidak_menampilkan_progress_dan_tim_pelaksana(): void
    {
        Http::fake([
            '*/api/v1/proposals/9' => Http::response([
                'id'                  => 9,
                'proposal_code'       => 'PRSL-2026-009',
                'client_company_name' => 'PT. Maju Bersama Sentosa',
                'title'               => 'Sistem Informasi Manajemen Teaching Factory',
                'background'          => 'Pembuatan sistem informasi manajemen teaching factory terintegrasi.',
                'category_id'         => 1,
                'category'            => ['id' => 1, 'name' => 'Web'],
                'status_id'           => 2,
                'status'              => ['id' => 2, 'name' => 'Submitted'],
                'desired_deadline'    => '2026-12-15',
                'estimated_budget'    => '30000000.00',
                'client'              => [
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'phone_number' => '6281234567890',
                ],
                'modules' => [
                    ['id' => 1, 'title' => 'Modul Manajemen Proposal', 'description' => 'Pengelolaan proposal proyek.'],
                ],
            ], 200),
            '*/api/v1/categories' => Http::response(['success' => true, 'data' => [['id' => 1, 'name' => 'Web']]], 200),
            '*/api/v1/users/company' => Http::response([
                'success' => true,
                'data'    => [
                    'company_name' => 'PT. Maju Bersama Sentosa',
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp'        => '6281234567890',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya/9');

        $response->assertOk();
        $response->assertDontSee('Progres Pengerjaan');
        $response->assertDontSee('Tim Pelaksana');
    }
}
