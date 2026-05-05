<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DeployPjCsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'absensi.view',
            'absensi.manage',
            'jadwal-cs.view',
            'jadwal-cs.manage',
            'cuti.view',
            'cuti.manage',
            'laporan.view',
            'lk-cs.view',
            'lk-cs.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $role = Role::firstOrCreate(['name' => 'pj_cs']);
        $role->syncPermissions($permissions);

        // Ensure chief has jadwal-cs.manage and laporan.view
        $chief = Role::firstOrCreate(['name' => 'chief']);
        $chief->givePermissionTo(['jadwal-cs.manage', 'laporan.view']);

        // Ensure koordinator has jadwal-cs.manage
        $koordinator = Role::firstOrCreate(['name' => 'koordinator']);
        $koordinator->givePermissionTo(['jadwal-cs.manage']);

        $this->command->info('pj_cs role seeded successfully.');
    }
}
