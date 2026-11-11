<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
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
        $response->assertSee('Belum ada proyek aktif.');
        $response->assertDontSee('PRJ-2026/014');
        $response->assertDontSee('Dr. Hendra Kusuma, M.T.');
        $response->assertSee('TOTAL PROYEK PORTOFOLIO');
        $response->assertSee('RATA - RATA PROGRES');
        $response->assertSee('0 Modul');
        $response->assertDontSee('Cari kode proyek, judul deliverable, nama dosen');
        $response->assertDontSee('Web Platform');
        $response->assertSee('Persetujuan BAST');
        $response->assertSee('Pengurus TeFa menyiapkan dokumen');
        $response->assertSee('Belum ada BAST yang menunggu persetujuan PIC Klien.');
        $response->assertDontSee('Isi formulir BAST');
    }

    public function test_proyek_dengan_uat_passed_muncul_di_daftar_bast(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([[
                'id' => 501,
                'project_code' => 'PRJ-2026/501',
                'title' => 'Proyek UAT Lulus',
                'bast_eligible' => true,
                'bast_prepared' => true,
                'bast_number' => 'BAST/TEFA/2026/001',
                'repository_url' => 'https://example.test/repository',
                'documentation_url' => 'handover-documents/api-documentation.pdf',
                'maintenance' => ['maintenance_warranty_months' => 3],
            ]], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya');

        $response->assertOk()
            ->assertSee('PRJ-2026/501')
            ->assertSee('Proyek UAT Lulus')
            ->assertSee('BAST/TEFA/2026/001')
            ->assertSee('Repository source code')
            ->assertSee('Nama PIC yang menyetujui')
            ->assertSee('Setujui BAST')
            ->assertDontSee('Isi formulir BAST');
    }

    public function test_daftar_proyek_menggunakan_data_api_dan_menampilkan_spk_terbitan(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([[
                'id' => 501,
                'project_code' => 'PRJ-2026/501',
                'spk_number' => 'SPK/TEFA/2026/011',
                'contract_file_url' => 'contracts/501-spk-tefa-2026-011.pdf',
                'title' => 'Sistem Inventaris Perusahaan',
                'category' => 'Web',
                'status' => 'Active',
                'overall_progress' => 65,
                'start_date' => '2026-11-01',
                'target_completion_date' => '2027-01-31',
                'pm' => ['name' => 'Dosen Backend', 'nidn' => '123456', 'department' => 'Teknik Informatika'],
                'modules' => [['title' => 'Manajemen Barang', 'description' => 'Master data stok']],
                'team_members' => [['name' => 'Mahasiswa Aktif', 'role' => 'Backend']],
            ]], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya');

        $response->assertOk()
            ->assertSee('PRJ-2026/501')
            ->assertSee('Sistem Inventaris Perusahaan')
            ->assertSee('Dosen Backend')
            ->assertSee('Manajemen Barang')
            ->assertSee('Mahasiswa Aktif')
            ->assertDontSee('PRJ-2026/014')
            ->assertDontSee('Sistem Monitoring Rantai Pasok Terpadu');
    }

    public function test_persetujuan_bast_dikirim_ke_api_milik_proposal_klien(): void
    {
        Http::fake([
            '*proposals/501/handover/approve' => Http::response(['success' => true], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->patch('/klien/projek-saya/501/bast/approve', [
                'client_signoff_name' => 'Klien Tevara',
                'client_approved' => '1',
            ]);

        $response->assertRedirect('/klien/projek-saya')
            ->assertSessionHas('success', 'Persetujuan BAST berhasil dikirim.');
        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/proposals/501/handover/approve'));
    }

    public function test_pengurus_menyiapkan_bast_di_halaman_proyek(): void
    {
        Http::fake([
            '*proposals/handover' => Http::response([[
                'id' => 501,
                'project_code' => 'PRJ-2026/501',
                'title' => 'Proyek UAT Lulus',
                'client_name' => 'PT Contoh',
                'latest_uat_result' => 'PASSED',
                'bast_eligible' => true,
                'bast_prepared' => false,
            ]], 200),
        ]);
        $session = [
            'api_token' => 'dummy-token',
            'user' => ['id' => 5, 'name' => 'Pengurus', 'roles' => ['pengurus_tefa']],
            'active_role' => 'pengurus_tefa',
        ];

        $this->withSession($session)
            ->get('/pengurus-tefa/projek')
            ->assertOk()
            ->assertSee('Siapkan BAST')
            ->assertSee('Lampiran fisik dokumentasi API')
            ->assertSee('Kirim ke PIC Klien');
    }

    public function test_pengurus_mengirimkan_isian_bast_dan_lampiran_ke_api(): void
    {
        Http::fake([
            '*proposals/501/handover/prepare' => Http::response(['success' => true], 200),
        ]);
        $session = [
            'api_token' => 'dummy-token',
            'user' => ['id' => 5, 'name' => 'Pengurus', 'roles' => ['pengurus_tefa']],
            'active_role' => 'pengurus_tefa',
        ];

        $this->withSession($session)
            ->post('/pengurus-tefa/proposals/501/bast', [
                'repository_url' => 'https://example.test/repository',
                'documentation_file' => UploadedFile::fake()->create('api-documentation.pdf', 20, 'application/pdf'),
                'user_manual_url' => 'https://example.test/manual',
                'maintenance_warranty_months' => '3',
            ])
            ->assertRedirect('/pengurus-tefa/projek')
            ->assertSessionHas('success', 'BAST berhasil disiapkan untuk persetujuan PIC Klien.');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/proposals/501/handover/prepare')
            && $request->hasFile('documentation_file'));
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
