<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_bisa_lihat_daftar_user(): void
    {
        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_admin_bisa_buat_user_baru(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'Ahmad Baru',
                'email'                 => 'ahmad.baru@test.com',
                'nip'                   => '198501012010011001',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'pjlp',
                'unit'                  => 'cleaning',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'ahmad.baru@test.com',
            'nip'   => '198501012010011001',
        ]);
    }

    public function test_nip_duplikat_ditolak(): void
    {
        User::factory()->create(['nip' => '198501012010011001']);

        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'User Lain',
                'email'                 => 'user.lain@test.com',
                'nip'                   => '198501012010011001',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'pjlp',
            ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_nip_dengan_huruf_ditolak(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'User Test',
                'email'                 => 'user.test@test.com',
                'nip'                   => 'SEC-001',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'pjlp',
            ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_nip_25_digit_diterima(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'User NIP Panjang',
                'email'                 => 'panjang@test.com',
                'nip'                   => '020193219840813201806049',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'pjlp',
                'unit'                  => 'security',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nip' => '020193219840813201806049']);
    }

    public function test_admin_bisa_update_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('pjlp');

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name'      => 'Nama Diubah',
                'email'     => $user->email,
                'role'      => 'pjlp',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Diubah']);
    }

    public function test_admin_bisa_hapus_user_lain(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pjlp');

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_tidak_bisa_hapus_diri_sendiri(): void
    {
        // Policy menolak self-delete sebelum sampai ke controller
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin))
            ->assertStatus(403);

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_email_duplikat_ditolak(): void
    {
        User::factory()->create(['email' => 'duplikat@test.com']);

        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'User Duplikat',
                'email'                 => 'duplikat@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'koordinator',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_kurang_8_karakter_ditolak(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'                  => 'User Test',
                'email'                 => 'test2@test.com',
                'password'              => '123',
                'password_confirmation' => '123',
                'role'                  => 'pjlp',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
