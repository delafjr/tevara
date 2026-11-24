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

    public function test_pengurus_tefa_can_access_pic_registration_page_with_company_dropdown(): void
    {
        Http::fake([
            '*companies' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 12,
                        'name' => 'PT Maju Bersama',
                        'email' => 'contact@majubersama.id',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->get('/pengurus-tefa/klien-mitra/registrasi-pic');

        $response->assertStatus(200);
        $response->assertSee('Registrasi');
        $response->assertSee('PIC Mitra / Klien');
        $response->assertSee('PT Maju Bersama');
        $response->assertSee('value="12"', false);
    }

    public function test_pengurus_tefa_can_submit_standalone_pic_registration(): void
    {
        Http::fake([
            '*users/pic' => Http::response([
                'success' => true,
                'message' => 'PIC berhasil didaftarkan.',
                'data' => [
                    'id' => 101,
                    'name' => 'Dewi Sartika',
                    'email' => 'dewi@majubersama.id',
                    'position' => 'Project Manager',
                ],
            ], 201),
        ]);

        $response = $this->withSession($this->sessionPengurus())
            ->post('/pengurus-tefa/klien-mitra/registrasi-pic', [
                'client_profile_id'     => 12,
                'name'                  => 'Dewi Sartika',
                'position'              => 'Project Manager',
                'email'                 => 'dewi@majubersama.id',
                'phone_number'          => '081233445566',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect('/pengurus-tefa/klien-mitra');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'users/pic')
                && (int) $request['client_profile_id'] === 12
                && $request['name'] === 'Dewi Sartika'
                && $request['position'] === 'Project Manager'
                && $request['password_confirmation'] === 'Password123!';
        });
    }
}
