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

    public function test_profile_page_renders_password_change_form_with_toggles_and_retype(): void
    {
        Http::fake([
            '*auth/me' => Http::response([
                'success' => true,
                'data' => [
                    'id'    => 10,
                    'name'  => 'Ahmad Dani',
                    'email' => 'ahmad.dani@mhs.polines.ac.id',
                    'roles' => ['mahasiswa'],
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
        $response->assertSee('Keamanan &amp; Ubah Password', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee('Ulangi Password Baru');
        $response->assertSee('Toggle password visibility');
    }

    public function test_user_can_submit_password_update_successfully(): void
    {
        Http::fake([
            '*auth/password' => Http::response([
                'success' => true,
                'message' => 'Password berhasil diperbarui',
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => ['id' => 10, 'name' => 'Ahmad Dani', 'roles' => ['mahasiswa']],
            'active_role' => 'mahasiswa',
        ])->put('/profile/password', [
            'password'              => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ]);

        $response->assertSessionHas('success', 'Password akun Anda berhasil diubah.');
        $response->assertRedirect();
    }

    public function test_password_update_validates_retype_confirmation(): void
    {
        $response = $this->withSession([
            'api_token'   => 'dummy-token',
            'user'        => ['id' => 10, 'name' => 'Ahmad Dani', 'roles' => ['mahasiswa']],
            'active_role' => 'mahasiswa',
        ])->put('/profile/password', [
            'password'              => 'NewSecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
