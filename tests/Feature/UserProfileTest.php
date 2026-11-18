<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page_with_single_role(): void
    {
        Http::fake([
            '*auth/me' => Http::response([
                'success' => true,
                'data' => [
                    'id'           => 10,
                    'name'         => 'Ahmad Dani',
                    'email'        => 'ahmad.dani@mhs.polines.ac.id',
                    'phone_number' => '08123456789',
                    'nim'          => '3.34.21.0.01',
                    'study_program'=> 'D3 Teknik Informatika',
                    'roles'        => ['mahasiswa'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => [
                'id'    => 10,
                'name'  => 'Ahmad Dani',
                'email' => 'ahmad.dani@mhs.polines.ac.id',
                'roles' => ['mahasiswa'],
            ],
            'active_role' => 'mahasiswa',
        ])->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Dani');
        $response->assertSee('ahmad.dani@mhs.polines.ac.id');
        $response->assertSee('3.34.21.0.01');
        $response->assertSee('D3 Teknik Informatika');
    }

    public function test_user_with_multiple_roles_displays_all_roles_and_switcher(): void
    {
        Http::fake([
            '*auth/me' => Http::response([
                'success' => true,
                'data' => [
                    'id'           => 25,
                    'name'         => 'Dr. Hendra Kusuma, M.T.',
                    'email'        => 'hendra@polines.ac.id',
                    'phone_number' => '08999999999',
                    'nidn'         => '0012345678',
                    'department'   => 'Teknik Elektro',
                    'is_pm'        => true,
                    'roles'        => ['dosen', 'pengurus_tefa'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => [
                'id'    => 25,
                'name'  => 'Dr. Hendra Kusuma, M.T.',
                'email' => 'hendra@polines.ac.id',
                'roles' => ['dosen', 'pengurus_tefa'],
            ],
            'active_role' => 'pengurus_tefa',
        ])->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Dr. Hendra Kusuma, M.T.');
        $response->assertSee('Dosen');
        $response->assertSee('Pengurus TeFa');
        $response->assertSee('Sedang Digunakan');
        $response->assertSee('Beralih ke Role Ini');
    }
}
