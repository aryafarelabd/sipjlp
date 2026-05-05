<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'dashboard.view',

            // PJLP
            'pjlp.view-self',
            'pjlp.view-unit',
            'pjlp.view-all',
            'pjlp.create',
            'pjlp.edit',
            'pjlp.delete',

            // Absensi
            'absensi.view-self',
            'absensi.view-unit',
            'absensi.view-all',
            'absensi.import',

            // Jadwal
            'jadwal.view-self',
            'jadwal.view-unit',
            'jadwal.view-all',
            'jadwal.manage',
            'jadwal-cs.manage',

            // Cuti
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.view-all',
            'cuti.approve',

            // Lembar Kerja
            'lembar-kerja.create',
            'lembar-kerja.view-self',
            'lembar-kerja.view-unit',
            'lembar-kerja.view-all',
            'lembar-kerja.validate',

            // Laporan
            'laporan.view',
            'laporan.export',

            // Master Data
            'master.manage',

            // User Management
            'user.manage',

            // Audit Log
            'audit-log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $pjlp = Role::create(['name' => 'pjlp']);
        $pjlp->givePermissionTo([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'jadwal.view-self',
            'cuti.create',
            'cuti.view-self',
            'lembar-kerja.create',
            'lembar-kerja.view-self',
        ]);

        $danru = Role::create(['name' => 'danru']);
        $danru->givePermissionTo([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'jadwal.view-self',
            'cuti.create',
            'cuti.view-self',
            'cuti.approve',
        ]);

        $chief = Role::create(['name' => 'chief']);
        $chief->givePermissionTo([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'absensi.view-unit',
            'jadwal.view-self',
            'jadwal.view-unit',
            'jadwal.manage',
            'jadwal-cs.manage',
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.approve',
            'laporan.view',
        ]);

        $pjCs = Role::create(['name' => 'pj_cs']);
        $pjCs->givePermissionTo([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'absensi.view-unit',
            'jadwal.view-self',
            'jadwal.view-unit',
            'jadwal-cs.manage',
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.approve',
            'laporan.view',
        ]);

        $koordinator = Role::create(['name' => 'koordinator']);
        $koordinator->givePermissionTo([
            'dashboard.view',
            'pjlp.view-unit',
            'absensi.view-unit',
            'jadwal.view-unit',
            'jadwal.manage',
            'jadwal-cs.manage',
            'cuti.view-unit',
            'cuti.approve',
            'lembar-kerja.view-unit',
            'lembar-kerja.validate',
            'laporan.view',
            'laporan.export',
        ]);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'dashboard.view',
            'pjlp.view-all',
            'pjlp.create',
            'pjlp.edit',
            'pjlp.delete',
            'absensi.view-all',
            'absensi.import',
            'jadwal.view-all',
            'jadwal.manage',
            'cuti.view-all',
            'cuti.approve',
            'lembar-kerja.view-all',
            'lembar-kerja.validate',
            'laporan.view',
            'laporan.export',
            'master.manage',
            'user.manage',
            'audit-log.view',
        ]);

        $manajemen = Role::create(['name' => 'manajemen']);
        $manajemen->givePermissionTo([
            'dashboard.view',
            'pjlp.view-all',
            'absensi.view-all',
            'jadwal.view-all',
            'cuti.view-all',
            'lembar-kerja.view-all',
            'laporan.view',
            'laporan.export',
        ]);
    }
}
