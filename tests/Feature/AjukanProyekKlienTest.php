<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AjukanProyekKlienTest extends TestCase
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
                'roles'        => ['klien'],
                'is_pm'        => false,
            ],
            'active_role' => 'klien',
        ];
    }

    public function test_halaman_ajukan_proyek_hanya_untuk_klien(): void
    {
        $this->withSession($this->sessionKlien())
            ->get('/klien/ajukan-proyek')
            ->assertStatus(200)
            ->assertSee('Ajukan Proyek');

        $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => ['id' => 1, 'name' => 'Mhs', 'roles' => ['mahasiswa']],
            'active_role' => 'mahasiswa',
        ])->get('/klien/ajukan-proyek')->assertStatus(403);
    }

    public function test_pengajuan_valid_dikirim_ke_backend(): void
    {
        Http::fake([
            '*/api/v1/proposals' => Http::response([
                'proposal_code' => 'PRSL-2026-0008',
                'id'            => 8,
            ], 201),
        ]);

        $response = $this->withSession($this->sessionKlien())->post('/klien/ajukan-proyek', [
            '_token'         => 'x',
            'nama_instansi'  => 'PT. Maju Bersama Sentosa',
            'pic_nama'       => 'Budi Santoso',
            'kontak_nomor'   => '6281234567890',
            'kontak_email'   => 'budi@majubersamasentosa.co.id',
            'judul_proyek'   => 'Sistem Informasi Manajemen Teaching Factory',
            'kategori_id'    => 1,
            'ringkasan'      => str_repeat('Kami membutuhkan sistem digital untuk mengelola pengajuan proyek Teaching Factory. ', 3),
            'deskripsi'      => 'Sistem harus mendukung multi role dan dashboard realtime.',
            'target_pengguna' => 'Staf IT perusahaan',
            'deadline'       => now()->addDays(60)->toDateString(),
            'anggaran'       => 45000000,
            'modul'          => json_encode([
                ['judul' => 'Modul Auth', 'deskripsi' => 'Login dan role'],
                ['judul' => 'Modul Proposal', 'deskripsi' => 'CRUD pengajuan'],
            ]),
        ]);

        $response->assertRedirect('/klien/ajukan-proyek');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:8000/api/v1/proposals'
                && $request['title'] === 'Sistem Informasi Manajemen Teaching Factory'
                && $request['category_id'] === 1
                && str_contains($request['background'], 'Modul Auth');
        });
    }

    public function test_pengajuan_tanpa_ringkasan_pendek_ditolak(): void
    {
        Http::fake();

        $this->withSession($this->sessionKlien())
            ->post('/klien/ajukan-proyek', [
                'nama_instansi' => 'PT. Maju Bersama Sentosa',
                'pic_nama'      => 'Budi Santoso',
                'kontak_email'  => 'budi@majubersamasentosa.co.id',
                'judul_proyek'  => 'Sistem Informasi',
                'kategori_id'   => 1,
                'ringkasan'     => 'terlalu singkat',
                'deadline'      => now()->addDays(60)->toDateString(),
                'modul'         => json_encode([['judul' => 'Modul Auth', 'deskripsi' => '']]),
            ])
            ->assertSessionHasErrors(['ringkasan']);

        Http::assertNothingSent();
    }
}
