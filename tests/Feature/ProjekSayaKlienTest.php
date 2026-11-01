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
}
