<?php

namespace Tests\Feature;

use App\Models\Pjlp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function test_halaman_login_dapat_diakses(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_login_dengan_email_berhasil(): void
    {
        $user = User::factory()->create([
            'email'     => 'admin@test.com',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('admin');

        $this->post(route('login'), [
            'login'    => 'admin@test.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_nip_berhasil(): void
    {
        $user = User::factory()->create([
            'nip'       => '198001012005011001',
            'email'     => 'pjlp.nip@test.com',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('pjlp');

        $this->post(route('login'), [
            'login'    => '198001012005011001',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_password_salah_ditolak(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('admin');

        $this->post(route('login'), [
            'login'    => 'test@test.com',
            'password' => 'salah',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $user = User::factory()->create([
            'email'     => 'nonaktif@test.com',
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);
        $user->assignRole('pjlp');

        $this->post(route('login'), [
            'login'    => 'nonaktif@test.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_tanpa_input_ditolak(): void
    {
        $this->post(route('login'), [])->assertSessionHasErrors(['login', 'password']);
    }

    public function test_logout_berhasil(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_halaman_dashboard_redirect_ke_login_jika_belum_auth(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
