<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RevisiProyekTest extends TestCase
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

    public function test_pengurus_tefa_buka_halaman_revisi_nama_terisi_otomatis_dan_readonly(): void
    {
        Http::fake([
            '*/api/v1/proposals/13/pending' => Http::response([
                'id'               => 13,
                'proposal_code'    => 'PRSL-2026-490',
                'client_name'      => 'PT. Maju Bersama Sentosa',
                'title'            => 'Portal Berita Digital',
                'background'       => 'Kami membutuhkan portal berita digital yang mudah dikelola redaksi.',
                'target_audience'  => 'Masyarakat umum dan tim redaksi',
                'category_name'    => 'Web',
                'status'           => 'Submitted',
                'desired_deadline' => '2027-05-25',
                'estimated_budget' => '30000000.00',
                'pic'              => [
                    'name'  => 'Budi Santoso',
                    'email' => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp' => '6281234567890',
                ],
                'modules'          => [
                    ['id' => 1, 'title' => 'Modul Berita', 'description' => 'Posting artikel'],
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/validasi-proposal/13/revisi');

        $response->assertStatus(200);
        $response->assertSee('Wiktasari, S.T., M.Kom.');
        $response->assertSee('readonly');
        $response->assertSee('PT. Maju Bersama Sentosa');
        $response->assertSee('Portal Berita Digital');
        $response->assertSee('Catatan Apa yang Perlu Direvisi Klien');
    }

    public function test_pengurus_tefa_submit_revisi_mengirimkan_patch_ke_backend(): void
    {
        Http::fake([
            '*/api/v1/proposals/13/review' => Http::response([
                'message' => 'Review submitted successfully',
                'data'    => [
                    'id'           => 13,
                    'status_id'    => 4,
                    'review_notes' => 'Mohon sesuaikan estimasi harga ke Rp 25.000.000',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/validasi-proposal/13/revisi', [
                'review_notes' => 'Mohon sesuaikan estimasi harga ke Rp 25.000.000',
            ]);

        $response->assertRedirect('/pengurus-tefa/validasi-proposal');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'proposals/13/review')
                && $request['decision'] === 'revision_required'
                && $request['review_notes'] === 'Mohon sesuaikan estimasi harga ke Rp 25.000.000';
        });
    }

    public function test_klien_buka_detail_projek_dengan_status_revisi_menampilkan_catatan_dan_form_mirip_ajukan_proyek(): void
    {
        Http::fake([
            '*/api/v1/proposals/13' => Http::response([
                'id'                  => 13,
                'proposal_code'       => 'PRSL-2026-490',
                'client_company_name' => 'PT. Maju Bersama Sentosa',
                'title'               => 'Portal Berita Digital',
                'background'          => 'Kami membutuhkan portal berita digital yang mudah dikelola redaksi dengan fitur CMS lengkap lebih dari 100 karakter.',
                'target_audience'     => 'Masyarakat umum dan tim redaksi',
                'category_id'         => 1,
                'category'            => ['id' => 1, 'name' => 'Web'],
                'status_id'           => 4,
                'status'              => ['id' => 4, 'name' => 'Revision Required'],
                'desired_deadline'    => '2027-05-25',
                'estimated_budget'    => '30000000.00',
                'review_notes'        => 'Mohon sesuaikan harga proyek menjadi 25000000.',
                'reviewer'            => ['id' => 5, 'name' => 'Wiktasari, S.T., M.Kom.'],
                'client'              => [
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'phone_number' => '6281234567890',
                ],
                'modules'             => [
                    ['id' => 1, 'title' => 'Modul Berita', 'description' => 'Posting artikel berita'],
                ],
            ]),
            '*/api/v1/categories' => Http::response([
                'success' => true,
                'data'    => [
                    ['id' => 1, 'name' => 'Web'],
                    ['id' => 2, 'name' => 'Mobile'],
                ],
            ]),
            '*/api/v1/users/company' => Http::response([
                'success' => true,
                'data'    => [
                    'company_name' => 'PT. Maju Bersama Sentosa',
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp'        => '6281234567890',
                    'position'     => 'IT Manager',
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/projek-saya/13');

        $response->assertStatus(200);
        $response->assertSee('Improvement (Revisi)');
        $response->assertSee('Catatan Perbaikan dari Pengurus TEFA');
        $response->assertSee('Wiktasari, S.T., M.Kom.');
        $response->assertSee('Mohon sesuaikan harga proyek menjadi 25000000.');
        $response->assertSee('Portal Berita Digital');
        $response->assertSee('Estimasi Anggaran / Harga');
        $response->assertSee('Kirim Perbaikan ke Pengurus TEFA');
    }

    public function test_klien_submit_perbaikan_mengirimkan_put_ke_backend_dan_redirect_ke_projek_saya(): void
    {
        Http::fake([
            '*/api/v1/proposals/13' => Http::response([
                'message' => 'Revisi proposal berhasil disimpan',
                'data'    => [
                    'id'               => 13,
                    'status_id'        => 2,
                    'estimated_budget' => 25000000,
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->post('/klien/projek-saya/13/revisi', [
                'judul_proyek'    => 'Portal Berita Digital (Revisi)',
                'kategori_id'     => 1,
                'ringkasan'       => 'Ini adalah ringkasan kebutuhan proyek yang diperbarui dan melebihi seratus karakter untuk memenuhi syarat validasi.',
                'target_pengguna' => 'Masyarakat umum dan tim redaksi online',
                'deadline'        => '2027-06-30',
                'anggaran'        => 25000000,
                'modul'           => json_encode([
                    ['judul' => 'Modul CMS Berita', 'deskripsi' => 'Pengelolaan artikel dan kategori'],
                ]),
            ]);

        $response->assertRedirect('/klien/projek-saya');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'proposals/13')
                && $request->method() === 'PUT'
                && (float) $request['estimated_budget'] === 25000000.0
                && $request['title'] === 'Portal Berita Digital (Revisi)';
        });
    }
}

