<?php

namespace Tests\Feature;

use Tests\TestCase;

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
}
