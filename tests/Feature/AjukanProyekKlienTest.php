<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AjukanProyekKlienTest extends TestCase
{
    /**
     * Fake dua endpoint yang dipanggil halaman ajukan proyek.
     *
     * @return array<string, \Illuminate\Http\Client\Response>
     */
    private function profileFakes(array $company = [], array $categories = []): array
    {
        return [
            '*/api/v1/users/company' => Http::response([
                'success' => true,
                'message' => 'Company profile retrieved successfully',
                'data'    => $company ?: [
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'no_hp'        => '6281234567890',
                    'company_name' => 'PT. Maju Bersama Sentosa',
                    'role'         => 'klien',
                ],
            ]),
            '*/api/v1/categories' => Http::response([
                'success' => true,
                'data'    => $categories ?: [
                    ['id' => 1, 'name' => 'Web'],
                    ['id' => 2, 'name' => 'Mobile'],
                    ['id' => 3, 'name' => 'Desktop'],
                    ['id' => 4, 'name' => 'IoT'],
                    ['id' => 5, 'name' => 'Other'],
                ],
            ]),
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

    public function test_halaman_ajukan_proyek_hanya_untuk_klien(): void
    {
        Http::fake($this->profileFakes());

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

    public function test_halaman_ajukan_proyek_menampilkan_data_profil(): void
    {
        Http::fake($this->profileFakes());

        $this->withSession($this->sessionKlien())
            ->get('/klien/ajukan-proyek')
            ->assertStatus(200)
            ->assertSee('Budi Santoso')
            ->assertSee('6281234567890')
            ->assertSee('budi.santoso@majubersamasentosa.co.id');
    }

    public function test_nama_instansi_diisi_dengan_nama_perusahaan_bukan_nama_pic(): void
    {
        Http::fake($this->profileFakes());

        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/ajukan-proyek')
            ->assertStatus(200);

        // Field instansi harus berisi nama perusahaan, bukan nama orang.
        $response->assertSee('value="PT. Maju Bersama Sentosa"', false);
        $response->assertSee('value="Budi Santoso"', false);
        // Jabatan PIC dari backend ditampilkan sebagai info.
        $response->assertSee('Jabatan PIC: IT Manager');
    }

    public function test_setelah_login_field_instansi_terisi_nama_perusahaan(): void
    {
        Http::fake($this->profileFakes() + [
            '*/api/v1/auth/login' => Http::response([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => [
                    'user'  => [
                        'id'           => 106,
                        'name'         => 'Budi Santoso',
                        'email'        => 'budi.santoso@majubersamasentosa.co.id',
                        'phone_number' => '6281234567890',
                        'company_name' => 'PT. Maju Bersama Sentosa',
                        'position'     => 'IT Manager',
                        'roles'        => ['klien'],
                        'is_pm'        => false,
                    ],
                    'token' => 'fake-token',
                ],
            ]),
        ]);

        $this->post('/auth/login', [
            'email'    => 'budi.santoso@majubersamasentosa.co.id',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->get('/klien/ajukan-proyek')
            ->assertStatus(200)
            ->assertSee('value="PT. Maju Bersama Sentosa"', false)
            ->assertSee('Jabatan PIC: IT Manager');
    }

    public function test_field_kosong_setelah_submit_gagal_jatuh_ke_profil(): void
    {
        Http::fake($this->profileFakes());

        // Simulasikan submit gagal: old input berisi string kosong.
        $this->withSession($this->sessionKlien() + ['_old_input' => ['nama_instansi' => '', 'kontak_nomor' => '']])
            ->get('/klien/ajukan-proyek')
            ->assertStatus(200)
            ->assertSee('value="Budi Santoso"', false)
            ->assertSee('value="6281234567890"', false);
    }

    public function test_halaman_ajukan_proyek_mengambil_company_dan_categories_dari_api(): void
    {
        Http::fake($this->profileFakes(
            [
                'name'         => 'Siti Aminah',
                'email'        => 'siti@perusahaan-baru.co.id',
                'no_hp'        => '6289999999999',
                'company_name' => 'PT. Perusahaan Baru',
                'role'         => 'klien',
            ],
            [
                ['id' => 10, 'name' => 'Web'],
                ['id' => 11, 'name' => 'Artificial Intelligence'],
                ['id' => 12, 'name' => 'Blockchain'],
            ],
        ));

        $response = $this->withSession($this->sessionKlien())->get('/klien/ajukan-proyek');

        $response->assertOk();
        // Nilai dari /users/company harus menimpa data session.
        $response->assertSee('value="PT. Perusahaan Baru"', false);
        $response->assertSee('value="Siti Aminah"', false);
        $response->assertSee('value="6289999999999"', false);
        $response->assertSee('value="siti@perusahaan-baru.co.id"', false);
        // Kategori baru dari /categories ikut tampil di dropdown.
        $response->assertSee('<option value="11"', false);
        $response->assertSee('Artificial Intelligence');
        $response->assertSee('Blockchain');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/users/company')
            && $request->hasHeader('Authorization'));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/categories')
            && !$request->hasHeader('Authorization'));
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

    public function test_target_pengguna_dan_anggaran_wajib_diisi(): void
    {
        Http::fake();

        $this->withSession($this->sessionKlien())
            ->post('/klien/ajukan-proyek', [
                'nama_instansi' => 'PT. Maju Bersama Sentosa',
                'pic_nama'      => 'Budi Santoso',
                'kontak_email'  => 'budi@majubersamasentosa.co.id',
                'judul_proyek'  => 'Sistem Informasi',
                'kategori_id'   => 1,
                'ringkasan'     => str_repeat('Kebutuhan sistem yang cukup panjang untuk lolos validasi. ', 3),
                'deadline'      => now()->addDays(60)->toDateString(),
                'modul'         => json_encode([['judul' => 'Modul Auth', 'deskripsi' => '']]),
            ])
            ->assertSessionHasErrors(['target_pengguna', 'anggaran']);

        Http::assertNothingSent();
    }
}
