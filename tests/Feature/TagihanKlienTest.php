<?php

namespace Tests\Feature;

use Tests\TestCase;

class TagihanKlienTest extends TestCase
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

    public function test_halaman_tagihan_bisa_diakses_klien(): void
    {
        $response = $this->withSession($this->sessionKlien())
            ->get('/klien/tagihan');

        $response->assertStatus(200);
        $response->assertSee('Tagihan & Pembayaran', false);
        $response->assertSee('Rp 185.000.000');
        $response->assertSee('TOTAL NILAI KONTRAK TERIKAT SPK');
        $response->assertSee('PRJ-2026/009');
        $response->assertSee('PRJ-2026/014');
        $response->assertSee('PRJ-2026/021');
        $response->assertSee('Unggah Bukti Pembayaran Termin Berjalan');
        $response->assertSee('142-00-9928172-1');
        $response->assertSee('88720-08123456789');
        $response->assertSee('Prosedur & Standar SLA Keuangan', false);
    }

    public function test_halaman_tagihan_dicegah_untuk_role_lain(): void
    {
        $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => ['id' => 1, 'name' => 'Mahasiswa User', 'roles' => ['mahasiswa']],
            'active_role' => 'mahasiswa',
        ])->get('/klien/tagihan')->assertStatus(403);
    }

    public function test_alias_tagihan_redirect(): void
    {
        $this->withSession($this->sessionKlien())
            ->get('/klien/tagihan-pembayaran')
            ->assertRedirect('/klien/tagihan');

        $this->withSession($this->sessionKlien())
            ->get('/klien/pembayaran')
            ->assertRedirect('/klien/tagihan');
    }
}
