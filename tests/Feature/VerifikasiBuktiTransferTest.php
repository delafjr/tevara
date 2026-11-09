<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifikasiBuktiTransferTest extends TestCase
{
    private function sessionPengurus(): array
    {
        return [
            'api_token'   => 'dummy-token',
            'user'        => [
                'id'     => 5,
                'name'   => 'Pengurus Keuangan TeFa',
                'email'  => 'keuangan@polines.ac.id',
                'roles'  => ['pengurus_tefa'],
                'is_pm'  => false,
            ],
            'active_role' => 'pengurus_tefa',
        ];
    }

    private function fakePendingPaymentsApi(): void
    {
        Http::fake([
            '*/api/v1/payments/pending' => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id'                 => 2,
                        'proposal_id'        => 1,
                        'term_sequence'      => 2,
                        'amount'             => 25200000,
                        'payment_date'       => '2026-11-01',
                        'payment_method'     => 'Transfer Bank (Giro Mandiri)',
                        'transfer_reference' => 'TRF20261101-089',
                        'proof_url'          => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800',
                        'status'             => 'PENDING',
                        'proposal'           => [
                            'id'           => 1,
                            'project_code' => 'PRSL-2025-001',
                            'title'        => 'Sistem Informasi Manajemen Teaching Factory',
                            'client'       => [
                                'name'         => 'Budi Santoso',
                                'email'        => 'budi.santoso@majubersamasentosa.co.id',
                                'company_name' => 'PT Maju Bersama Sentosa',
                            ],
                        ],
                    ],
                ],
            ]),
            '*/api/v1/proposals' => Http::response([
                ['id' => 1, 'proposal_code' => 'PRSL-2025-001', 'title' => 'Sistem Informasi Manajemen Teaching Factory'],
            ]),
            '*/api/v1/proposals/1' => Http::response([
                'id'            => 1,
                'proposal_code' => 'PRSL-2025-001',
                'title'         => 'Sistem Informasi Manajemen Teaching Factory',
                'client'        => [
                    'name'         => 'Budi Santoso',
                    'email'        => 'budi.santoso@majubersamasentosa.co.id',
                    'company_name' => 'PT Maju Bersama Sentosa',
                ],
                'payments'      => [
                    [
                        'id'                 => 2,
                        'term_sequence'      => 2,
                        'amount'             => 25200000,
                        'payment_date'       => '2026-11-01',
                        'payment_method'     => 'Transfer Bank (Giro Mandiri)',
                        'transfer_reference' => 'TRF20261101-089',
                        'proof_url'          => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800',
                        'status'             => 'PENDING',
                    ],
                ],
            ]),
        ]);
    }

    public function test_halaman_keuangan_menampilkan_tautan_ke_page_verifikasi_bukti(): void
    {
        $this->fakePendingPaymentsApi();

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/keuangan');

        $response->assertOk();
        $response->assertSee('Antrean Verifikasi Bukti Pembayaran');
        $response->assertSee(route('pengurus-tefa.payments.verifikasi', ['proposal' => 1, 'payment' => 2]));
        $response->assertSee('Periksa');
        $response->assertSee('Verifikasi');
    }

    public function test_halaman_verifikasi_bukti_transfer_dapat_diakses_dan_menampilkan_detail(): void
    {
        $this->fakePendingPaymentsApi();

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/proposals/1/payments/2/verifikasi');

        $response->assertOk();
        $response->assertSee('Verifikasi Bukti');
        $response->assertSee('Transfer Pembayaran');
        $response->assertSee('Sistem Informasi Manajemen Teaching Factory');
        $response->assertSee('PRSL-2025-001');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Termin 2');
        $response->assertSee('25.200.000');
        $response->assertSee('Transfer Bank (Giro Mandiri)');
        $response->assertSee('Dokumen Bukti Transfer Bank');
        $response->assertSee('Form Keputusan Verifikasi');
        $response->assertSee(route('pengurus-tefa.payments.verify', ['proposal' => 1, 'payment' => 2]));
    }

    public function test_verifikasi_bukti_disetujui_berhasil_dan_redirect_ke_keuangan(): void
    {
        Http::fake([
            '*/api/v1/proposals/1/payments/2/verify' => Http::response([
                'success' => true,
                'message' => 'Payment verified successfully.',
            ]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/proposals/1/payments/2/verify', [
                'status'             => 'VERIFIED',
                'verification_notes' => 'Bukti sah dan dana masuk ke rekening.',
            ]);

        $response->assertRedirect(route('pengurus-tefa.keuangan'));
        $response->assertSessionHas('success', 'Pembayaran berhasil diverifikasi (Status Lunas).');
    }

    public function test_verifikasi_bukti_ditolak_berhasil_dan_redirect_ke_keuangan(): void
    {
        Http::fake([
            '*/api/v1/proposals/1/payments/2/verify' => Http::response([
                'success' => true,
                'message' => 'Payment rejected.',
            ]),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/proposals/1/payments/2/verify', [
                'status'             => 'REJECTED',
                'verification_notes' => 'Nominal transfer tidak sesuai tagihan.',
            ]);

        $response->assertRedirect(route('pengurus-tefa.keuangan'));
        $response->assertSessionHas('success', 'Bukti pembayaran telah ditolak dengan catatan.');
    }
}
