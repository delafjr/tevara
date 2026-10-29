<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ValidasiProposalTest extends TestCase
{
    private function sessionPengurus(): array
    {
        return [
            'api_token'   => 'dummy-token',
            'user'        => [
                'id'     => 5,
                'name'   => 'Wiktasari, S.T., M.Kom.',
                'email'  => 'wiktasari@polines.ac.id',
                'roles'  => ['pengurus_tefa'],
                'is_pm'  => false,
            ],
            'active_role' => 'pengurus_tefa',
        ];
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/api/v1/proposals/pending' => Http::response([
                [
                    'id'               => 13,
                    'proposal_code'    => 'PRSL-2026-490',
                    'client_name'      => 'PT. Maju Bersama Sentosa',
                    'title'            => 'Portal Berita Digital',
                    'background'       => 'Kami membutuhkan portal berita digital yang mudah dikelola redaksi.',
                    'target_audience'  => 'Masyarakat umum dan tim redaksi',
                    'category_name'    => 'Web',
                    'status'           => 'Submitted',
                    'desired_deadline' => '2027-05-25',
                    'estimated_budget' => '100000.00',
                ],
                [
                    'id'               => 9,
                    'proposal_code'    => 'PRSL-2026-372',
                    'client_name'      => 'PT. Digital Solusi Indonesia',
                    'title'            => 'Aplikasi Mobile Company Profile',
                    'category_name'    => 'Mobile',
                    'status'           => 'Revision Required',
                    'desired_deadline' => '2027-02-01',
                    'estimated_budget' => '10000000.00',
                ],
            ]),
            '*/api/v1/proposals' => Http::response([
                ['id' => 13, 'status' => 'Submitted'],
                ['id' => 9, 'status' => 'Revision Required'],
                ['id' => 8, 'status' => 'Approved'],
                ['id' => 7, 'status' => 'Rejected'],
            ]),
        ]);
    }

    public function test_halaman_validasi_proposal_mengambil_data_pending_dari_api(): void
    {
        $this->fakeApi();

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal');

        $response->assertOk();
        $response->assertSee('PRSL-2026-490');
        $response->assertSee('Portal Berita Digital');
        $response->assertSee('PT. Maju Bersama Sentosa');
        $response->assertSee('Aplikasi Mobile Company Profile');
        $response->assertSee('Rp 10.000.000');
        $response->assertSee('Kami membutuhkan portal berita digital yang mudah dikelola redaksi.');
        $response->assertSee('Masyarakat umum dan tim redaksi');
        $response->assertSee('Latar Belakang');
        $response->assertSee('Target Pengguna');
        $response->assertSee('2 Proposal');
        $response->assertSee('Lihat Detail');
        $response->assertSee(route('pengurus-tefa.validasi-proposal.detail', 13), false);
        $response->assertDontSee('Setujui');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/proposals/pending')
            && $request->hasHeader('Authorization'));
    }

    public function test_halaman_detail_proposal_mengambil_data_dari_endpoint_pending_detail(): void
    {
        Http::fake([
            '*/api/v1/proposals/13/pending' => Http::response([
                'id'               => 13,
                'proposal_code'    => 'PRSL-2026-490',
                'title'            => 'lorep ipsum try from web',
                'background'       => 'buatin mobil tesla yg bisa terbanggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg',
                'target_audience'  => 'seluruh manusia dimuka bumi',
                'category_name'    => 'Other',
                'status'           => 'Submitted',
                'desired_deadline' => '2027-05-25',
                'estimated_budget' => '100000.00',
                'client_name'      => 'PT. Maju Bersama Sentosa',
                'pic'              => [
                    'name'  => 'Budi Santoso',
                    'email' => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp' => '6281234567890',
                ],
                'modules' => [
                    ['id' => 17, 'title' => 'terbang', 'description' => 'yg bisa terbang'],
                    ['id' => 18, 'title' => 'jalan vertikal', 'description' => 'yg bisa jalan vertikal'],
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal/13');

        $response->assertOk();
        $response->assertSee('lorep ipsum try from web');
        $response->assertSee('buatin mobil tesla yg bisa terbang');
        $response->assertSee('seluruh manusia dimuka bumi');
        $response->assertSee('PT. Maju Bersama Sentosa');
        $response->assertSee('Budi Santoso');
        $response->assertSee('budi.santoso@majubersamasentosa.co.id');
        $response->assertSee('6281234567890');
        $response->assertSee('terbang');
        $response->assertSee('yg bisa terbang');
        $response->assertSee('jalan vertikal');
        $response->assertSee('yg bisa jalan vertikal');
        $response->assertSee('Setujui');
        $response->assertSee('Butuh Revisi');
        $response->assertSee('Tolak');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/proposals/13/pending')
            && $request->hasHeader('Authorization'));
    }

    public function test_halaman_detail_proposal_menampilkan_error_bila_tidak_ditemukan(): void
    {
        Http::fake([
            '*/api/v1/proposals/99/pending' => Http::response([
                'message' => 'Proposal pending tidak ditemukan.',
            ], 404),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal/99');

        $response->assertOk();
        $response->assertSee('Detail proposal tidak dapat ditampilkan');
        $response->assertSee('Proposal pending tidak ditemukan.');
    }

    public function test_halaman_validasi_proposal_memakai_grid_penuh_tanpa_pm_mahasiswa_dan_riwayat(): void
    {
        $this->fakeApi();

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal');

        $response->assertOk();
        $response->assertSee('grid grid-cols-1 gap-4', false);
        $response->assertDontSee('PM:');
        $response->assertDontSee('mhs');
        $response->assertDontSee('Riwayat Validasi');
    }

    public function test_halaman_validasi_proposal_menampilkan_empty_state(): void
    {
        Http::fake([
            '*/api/v1/proposals/pending' => Http::response([]),
            '*/api/v1/proposals'         => Http::response([]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal');

        $response->assertOk();
        $response->assertSee('Belum ada proposal yang menunggu validasi');
    }

    public function test_tombol_setujui_mengarahkan_ke_halaman_lanjutan_persetujuan(): void
    {
        $this->fakeDetailApi();

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal/13');

        $response->assertOk();
        $response->assertSee(route('pengurus-tefa.validasi-proposal.setujui', 13), false);
    }

    public function test_halaman_setujui_proposal_dapat_diakses_dan_memuat_semua_kolom(): void
    {
        $this->fakeDetailApi();
        Http::fake([
            '*/api/v1/proposals/13/pending' => Http::response([
                'id'               => 13,
                'proposal_code'    => 'PRSL-2026-490',
                'title'            => 'Portal Berita Digital',
                'client_name'      => 'PT. Maju Bersama Sentosa',
                'estimated_budget' => '100000.00',
                'desired_deadline' => '2027-05-25',
                'background'       => 'Scope kerja sistem informasi.',
            ]),
            '*/api/v1/users?role=dosen' => Http::response([
                'success' => true,
                'data'    => [
                    ['id' => 3, 'name' => 'Prof. Kurnia', 'email' => 'kurnia@polines.ac.id', 'nidn' => '12345'],
                ],
            ]),
            '*/api/v1/users?role=mahasiswa' => Http::response([
                'success' => true,
                'data'    => [
                    ['id' => 50, 'name' => 'Andi Pratama', 'email' => 'andi@mhs.polines.ac.id', 'nim' => '226411001', 'study_program' => 'D3 TI'],
                ],
            ]),
            '*/api/v1/master/project-roles' => Http::response([
                'success' => true,
                'data'    => [
                    ['id' => 1, 'name' => 'UI/UX'],
                    ['id' => 2, 'name' => 'Frontend'],
                    ['id' => 3, 'name' => 'Backend'],
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal/13/setujui');

        $response->assertOk();
        $response->assertSee('Persetujuan Proposal &amp; Pembentukan Proyek', false);
        $response->assertSee('Surat Perjanjian Kerja Sama &amp; SPK', false);
        $response->assertSee('Biaya yang Disepakati &amp; Skema Termin', false);
        $response->assertSee('Pembentukan Tim Pengembang TeFa', false);
        $response->assertSee('Project Manager (Dosen TeFa)', false);
        $response->assertSee('Berkas Surat Perjanjian / Kontrak (SPK)', false);
    }

    public function test_proses_setujui_proposal_berhasil_menyimpan_dan_redirect(): void
    {
        Http::fake([
            '*/api/v1/proposals/13/contract' => Http::response([
                'message' => 'Data SPK/kontrak berhasil disimpan',
            ], 200),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/validasi-proposal/13/setujui', [
                'spk_number'                  => 'SPK/TEFA/2026/013',
                'scope_of_work'               => 'Pengerjaan modul frontend dan backend.',
                'total_amount'                => 50000000,
                'payment_terms_description'   => '3 Termin pembayaran',
                'maintenance_warranty_months' => 3,
                'start_date'                  => '2026-11-01',
                'target_completion_date'      => '2027-02-28',
                'pm_id'                       => 3,
                'terms'                       => json_encode([
                    ['term_sequence' => 1, 'amount' => 15000000, 'name' => 'Termin 1 (DP)'],
                    ['term_sequence' => 2, 'amount' => 35000000, 'name' => 'Termin 2 (Pelunasan)'],
                ]),
                'members'                     => json_encode([
                    ['student_id' => 50, 'role_id' => 2, 'joined_at' => '2026-11-01'],
                ]),
            ]);

        $response->assertRedirect(route('pengurus-tefa.validasi-proposal'));
        $response->assertSessionHas('success');
    }
}
