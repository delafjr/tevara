<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
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
        $response->assertSee('Total Tagihan');
        $response->assertSee('Belum ada tagihan proyek.');
        $response->assertDontSee('PRJ-2026/009');
        $response->assertDontSee('142-00-9928172-1');
        $response->assertDontSee('88720-08123456789');
        $response->assertDontSee('Belum tersedia');
    }

    public function test_invoice_and_payment_status_are_built_from_api_data(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([['id' => 501]], 200),
            '*proposals/501' => Http::response([
                'id' => 501,
                'proposal_code' => 'PRSL-2026-501',
                'spk_number' => 'SPK/TEFA/2026/501',
                'title' => 'Sistem Inventaris Perusahaan',
                'total_amount' => 2500000,
                'status' => ['name' => 'Active'],
                'payments' => [[
                    'id' => 601,
                    'invoice_number' => 'INV/2026/000601',
                    'term_sequence' => 1,
                    'amount' => 2500000,
                    'status' => 'PENDING',
                    'proof_url' => null,
                    'payment_method' => null,
                ]],
            ], 200),
        ]);

        $this->withSession($this->sessionKlien())
            ->get('/klien/tagihan')
            ->assertOk()
            ->assertSee('INV/2026/000601')
            ->assertSee('Sistem Inventaris Perusahaan')
            ->assertSee('SPK/TEFA/2026/501')
            ->assertSee(route('klien.tagihan.invoice', 601), false)
            ->assertDontSee('PRJ-2026/009');
    }

    public function test_invoice_pdf_is_downloaded_from_the_authenticated_api(): void
    {
        Http::fake([
            '*payments/601/invoice' => Http::response('%PDF-1.4 invoice', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="INV-2026-000601.pdf"',
            ]),
        ]);

        $this->withSession($this->sessionKlien())
            ->get(route('klien.tagihan.invoice', 601))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSee('%PDF-1.4 invoice');
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
