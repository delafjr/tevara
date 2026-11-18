<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PengurusRegistrasiPerusahaanTest extends TestCase
{
    private function sessionPengurus(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 5,
                'name' => 'Wiktasari, S.T., M.Kom.',
                'email' => 'wiktasari@polines.ac.id',
                'roles' => ['pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ];
    }

    private function sessionDosen(): array
    {
        return [
            'api_token' => 'dummy-token',
            'user' => [
                'id' => 3,
                'name' => 'Dosen PM',
                'email' => 'dosen@polines.ac.id',
                'roles' => ['dosen'],
            ],
            'active_role' => 'dosen',
        ];
    }

    public function test_non_pengurus_tefa_cannot_access_registrasi_perusahaan(): void
    {
        $response = $this->withSession($this->sessionDosen())
            ->get('/pengurus-tefa/klien-mitra/registrasi');

        $response->assertStatus(403);
    }

    public function test_pengurus_tefa_can_access_registrasi_perusahaan_page(): void
    {
        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/klien-mitra/registrasi');

        $response->assertStatus(200);
        $response->assertSee('Registrasi');
        $response->assertSee('Perusahaan / Mitra');
        $response->assertSee('Nama Perusahaan / Instansi');
        $response->assertSee('Email Resmi Perusahaan');
    }

    public function test_pengurus_tefa_can_submit_company_registration(): void
    {
        Http::fake([
            '*users/company' => Http::response([
                'success' => true,
                'message' => 'Perusahaan berhasil didaftarkan.',
                'data' => [
                    'id' => 99,
                    'name' => 'PT Semesta Solusi Digital',
                    'email' => 'corp@semesta.id',
                ],
            ], 201),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/klien-mitra/registrasi', [
                'name'                  => 'PT Semesta Solusi Digital',
                'email'                 => 'corp@semesta.id',
                'password'              => 'SecretPass123!',
                'password_confirmation' => 'SecretPass123!',
                'phone_number'          => '024-7654321',
                'address'               => 'Jl. Tembalang Raya No. 10, Semarang',
                'client_type'           => 'EXTERNAL',
                'pic_name'              => 'Andi Prasetyo',
                'pic_email'             => 'andi@semesta.id',
                'pic_position'          => 'VP Technology',
            ]);

        $response->assertRedirect('/pengurus-tefa/klien-mitra');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'users/company')
                && $request['name'] === 'PT Semesta Solusi Digital'
                && $request['email'] === 'corp@semesta.id'
                && $request['pic_name'] === 'Andi Prasetyo';
        });
    }
}
