<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UatWorkflowPageTest extends TestCase
{
    private function clientSession(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => ['id' => 51, 'name' => 'PIC Klien', 'roles' => ['klien']],
            'active_role' => 'klien',
        ];
    }

    private function pengurusSession(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => ['id' => 7, 'name' => 'Pengurus', 'roles' => ['pengurus_tefa']],
            'active_role' => 'pengurus_tefa',
        ];
    }

    private function pmSession(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => ['id' => 19, 'name' => 'Dosen PM', 'roles' => ['dosen']],
            'active_role' => 'dosen',
        ];
    }

    public function test_client_page_lets_client_submit_a_test_result_and_reads_api_data(): void
    {
        Http::fake([
            '*proposals/501' => Http::response([
                'id' => 501,
                'proposal_code' => 'PRSL-2026-501',
                'title' => 'Portal Mitra',
                'status' => ['name' => 'Testing'],
                'modules' => [[
                    'title' => 'Login',
                    'description' => 'Masuk menggunakan akun Klien.',
                ]],
            ]),
            '*proposals/501/uat' => Http::response(null, 404),
        ]);

        $this->withSession($this->clientSession())
            ->get('/klien/projek-saya/501/uat')
            ->assertOk()
            ->assertSee('Portal Mitra')
            ->assertSee('Kirim hasil untuk diperiksa')
            ->assertSee('Masuk menggunakan akun Klien.');
    }

    public function test_client_submit_route_forwards_result_to_the_api(): void
    {
        Http::fake([
            '*proposals/501/uat' => Http::response(['success' => true], 201),
        ]);

        $this->withSession($this->clientSession())
            ->post('/klien/projek-saya/501/uat', [
                'items' => [[
                    'feature_name' => 'Login',
                    'test_scenario' => 'Masuk dengan akun terdaftar.',
                    'result' => 'PASSED',
                    'client_feedback' => 'Sesuai.',
                ]],
            ])
            ->assertRedirect('/klien/projek-saya/501/uat')
            ->assertSessionHas('success');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/proposals/501/uat')
            && $request['items'][0]['result'] === 'PASSED');
    }

    public function test_pengurus_page_shows_review_form_and_team_members_from_api(): void
    {
        Http::fake([
            '*proposals/501/uat' => Http::response(['data' => [
                'id' => 11,
                'review_status' => 'PENDING_REVIEW',
                'overall_result' => 'FAILED',
                'session_date' => '2026-11-17',
                'uat_items' => [[
                    'id' => 23,
                    'feature_name' => 'Unduh laporan',
                    'result' => 'FAILED',
                    'bug_description' => 'Berkas tidak terunduh.',
                ]],
            ]]),
            '*proposals/501/team' => Http::response([[
                'student' => ['id' => 81, 'name' => 'Mahasiswa Satu', 'nim' => '22001'],
            ]]),
            '*proposals/501' => Http::response([
                'id' => 501,
                'proposal_code' => 'PRSL-2026-501',
                'title' => 'Portal Mitra',
                'client' => ['name' => 'PIC Klien', 'company_name' => 'Contoh PT'],
            ]),
        ]);

        $this->withSession($this->pengurusSession())
            ->get('/pengurus-tefa/proposals/501/uat')
            ->assertOk()
            ->assertSee('Pemeriksaan hasil UAT')
            ->assertSee('Unduh laporan')
            ->assertSee('Jelaskan apa yang perlu diperbaiki.')
            ->assertSee('Mahasiswa Satu')
            ->assertSee('Kirim checklist ke mahasiswa');
    }

    public function test_pm_can_view_uat_summary_without_client_or_pengurus_actions(): void
    {
        Http::fake([
            '*proposals/501/uat' => Http::response(['data' => [
                'id' => 11,
                'review_status' => 'APPROVED',
                'overall_result' => 'PASSED',
                'session_date' => '2026-11-17',
                'uat_items' => [[
                    'feature_name' => 'Login',
                    'result' => 'PASSED',
                    'test_scenario' => 'Masuk menggunakan akun terdaftar.',
                ]],
            ]]),
            '*proposals/501' => Http::response([
                'id' => 501,
                'proposal_code' => 'PRSL-2026-501',
                'title' => 'Portal Mitra',
            ]),
        ]);

        $this->withSession($this->pmSession())
            ->get('/pm/proposals/501/uat')
            ->assertOk()
            ->assertSee('Ringkasan UAT · hanya untuk dilihat')
            ->assertSee('Portal Mitra')
            ->assertSee('Masuk menggunakan akun terdaftar.')
            ->assertDontSee('Kirim checklist ke mahasiswa')
            ->assertDontSee('Sahkan hasil UAT');
    }
}
