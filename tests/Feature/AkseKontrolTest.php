<?php

namespace Tests\Feature;

use App\Enums\UnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test akses kontrol: pastikan setiap role hanya bisa akses halaman yang sesuai.
 */
class AkseKontrolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    private function makeUser(string $role, ?UnitType $unit = null): User
    {
        $user = User::factory()->create(['is_active' => true, 'unit' => $unit]);
        $user->assignRole($role);
        return $user;
    }

    // ─── Admin-only routes ────────────────────────────────────────────────────

    public function test_pjlp_tidak_bisa_akses_manajemen_user(): void
    {
        $this->actingAs($this->makeUser('pjlp'))
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_koordinator_tidak_bisa_akses_manajemen_user(): void
    {
        $this->actingAs($this->makeUser('koordinator', UnitType::SECURITY))
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_manajemen_tidak_bisa_akses_manajemen_user(): void
    {
        $this->actingAs($this->makeUser('manajemen'))
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_bisa_akses_manajemen_user(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('users.index'))
            ->assertOk();
    }

    // ─── Rekap absensi ────────────────────────────────────────────────────────

    public function test_pjlp_tidak_bisa_akses_rekap_absensi(): void
    {
        $this->actingAs($this->makeUser('pjlp'))
            ->get(route('absensi.rekap'))
            ->assertForbidden();
    }

    public function test_koordinator_bisa_akses_rekap_absensi(): void
    {
        $this->actingAs($this->makeUser('koordinator', UnitType::CLEANING))
            ->get(route('absensi.rekap'))
            ->assertOk();
    }

    public function test_admin_bisa_akses_rekap_absensi(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('absensi.rekap'))
            ->assertOk();
    }

    // ─── Audit log ────────────────────────────────────────────────────────────

    public function test_pjlp_tidak_bisa_akses_audit_log(): void
    {
        $this->actingAs($this->makeUser('pjlp'))
            ->get(route('audit-log.index'))
            ->assertForbidden();
    }

    public function test_koordinator_tidak_bisa_akses_audit_log(): void
    {
        $this->actingAs($this->makeUser('koordinator', UnitType::SECURITY))
            ->get(route('audit-log.index'))
            ->assertForbidden();
    }

    public function test_admin_bisa_akses_audit_log(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('audit-log.index'))
            ->assertOk();
    }

    // ─── Guest tidak bisa akses halaman dalam ─────────────────────────────────

    public function test_guest_redirect_ke_login_dari_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_guest_redirect_ke_login_dari_absen(): void
    {
        $this->get(route('absen.index'))->assertRedirect(route('login'));
    }

    public function test_guest_redirect_ke_login_dari_rekap(): void
    {
        $this->get(route('absensi.rekap'))->assertRedirect(route('login'));
    }
}
