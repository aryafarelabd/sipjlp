<?php

namespace Tests\Feature;

use App\Enums\UnitType;
use App\Models\Pjlp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_admin_dapat_diakses(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_koordinator_security_dapat_diakses(): void
    {
        $this->actingAs($this->makeUser('koordinator', UnitType::SECURITY))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_koordinator_cs_dapat_diakses(): void
    {
        $this->actingAs($this->makeUser('koordinator', UnitType::CLEANING))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_manajemen_dapat_diakses(): void
    {
        $this->actingAs($this->makeUser('manajemen'))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_pjlp_dapat_diakses(): void
    {
        $user = $this->makeUser('pjlp', UnitType::CLEANING);
        Pjlp::factory()->create(['user_id' => $user->id, 'unit' => UnitType::CLEANING]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
