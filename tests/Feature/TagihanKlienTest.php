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

    public function test_klien_dapat_mengunggah_bukti_pembayaran_dan_diteruskan_ke_api(): void
    {
        Http::fake([
            '*/proposals/501/payments/601/proof' => Http::response([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload.',
                'data' => [
                    'id' => 601,
                    'proof_url' => 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
                    'status' => 'PENDING',
                ],
            ], 200),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg');

        $response = $this->withSession($this->sessionKlien())
            ->post(route('klien.tagihan.upload-proof', ['proposal' => 501, 'payment' => 601]), [
                'payment_date' => '2026-11-26',
                'payment_method' => 'BANK_TRANSFER',
                'transfer_reference' => 'TRF-123456',
                'proof' => $file,
            ]);

        $response->assertRedirect(route('klien.tagihan'));
        $response->assertSessionHas('success', 'Bukti pembayaran berhasil diunggah! Pembayaran sedang dalam antrean verifikasi Pengurus TEFA.');

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), 'proposals/501/payments/601/proof')
                && $request->isMultipart();
        });
    }

    public function test_validasi_unggah_bukti_pembayaran(): void
    {
        $response = $this->withSession($this->sessionKlien())
            ->post(route('klien.tagihan.upload-proof', ['proposal' => 501, 'payment' => 601]), [
                'payment_date' => '',
                'payment_method' => 'INVALID_METHOD',
            ]);

        $response->assertSessionHasErrors(['payment_date', 'payment_method', 'proof']);
    }
}
