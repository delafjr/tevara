<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BastKlienTest extends TestCase
{
    private function sessionKlien(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 106,
                'name' => 'Budi Santoso',
                'roles' => ['klien'],
            ],
            'active_role' => 'klien',
        ];
    }

    public function test_daftar_bast_menggunakan_kelayakan_uat_terbaru_dan_masa_garansi_dari_api(): void
    {
        Http::fake([
            '*proposals/mine' => Http::response([
                [
                    'id' => 42,
                    'project_code' => 'PRJ-2026/042',
                    'title' => 'Portal Mitra',
                    'status' => 'Handover',
                    'latest_uat_result' => 'PASSED',
                    'uat_signed_off_at' => '2026-11-20T10:00:00+00:00',
                    'bast_eligible' => true,
                    'bast_prepared' => true,
                    'client_approved' => false,
                    'warranty' => ['has_warranty' => true, 'warranty_months' => 6],
                ],
                [
                    'id' => 43,
                    'project_code' => 'PRJ-2026/043',
                    'title' => 'Aplikasi Inventaris',
                    'status' => 'Testing',
                    'latest_uat_result' => 'FAILED',
                    'bast_eligible' => false,
                    'bast_prepared' => false,
                    'client_approved' => false,
                    'warranty' => ['has_warranty' => false],
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionKlien())->get(route('klien.bast'));

        $response->assertOk()
            ->assertSee('Portal Mitra')
            ->assertSee('PASSED')
            ->assertSee('6 bulan')
            ->assertSee(route('klien.bast.show', 42), false)
            ->assertSee('Aplikasi Inventaris')
            ->assertSee('FAILED')
            ->assertSee('BAST belum dapat disetujui karena laporan UAT terbaru belum lulus.')
            ->assertDontSee(route('klien.bast.show', 43), false);
    }

    public function test_detail_bast_mengambil_durasi_garansi_tersimpan_dan_mengunci_persetujuan_jika_uat_terbaru_gagal(): void
    {
        Http::fake([
            '*proposals/43/handover' => Http::response([
                'success' => true,
                'data' => [
                    'proposal' => [
                        'id' => 43,
                        'title' => 'Aplikasi Inventaris',
                        'bast_number' => 'BAST/TEFA/2026/043',
                        'has_warranty' => true,
                        'warranty_months' => 6,
                        'handover_deliverables' => [],
                        'client_approved' => false,
                    ],
                    'latest_uat' => [
                        'overall_result' => 'FAILED',
                        'signed_off_at' => null,
                    ],
                    'eligible_for_handover' => false,
                    'awaiting_client_approval' => true,
                ],
            ]),
        ]);

        $response = $this->withSession($this->sessionKlien())->get(route('klien.bast.show', 43));

        $response->assertOk()
            ->assertSee('FAILED')
            ->assertSee('6 bulan')
            ->assertSee('BAST belum dapat disetujui.')
            ->assertDontSee(route('klien.bast.approve', 43), false)
            ->assertDontSee('name="warranty_months"', false);
    }

    public function test_persetujuan_bast_mengirim_nama_pic_tanpa_mengubah_masa_garansi(): void
    {
        Http::fake([
            '*proposals/42/handover/approve' => Http::response([
                'success' => true,
                'message' => 'BAST berhasil disetujui.',
            ]),
        ]);

        $response = $this->withSession($this->sessionKlien())
            ->post(route('klien.bast.approve', 42), [
                'client_signoff_name' => 'Budi Santoso',
                'client_approved' => '1',
            ]);

        $response->assertRedirect(route('klien.bast.show', 42));
        $this->assertSame('BAST berhasil disetujui.', session('success'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/proposals/42/handover/approve')
            && $request['client_signoff_name'] === 'Budi Santoso'
            && $request['client_approved'] === true
            && ! array_key_exists('maintenance_warranty_months', $request->data())
        );
    }
}
