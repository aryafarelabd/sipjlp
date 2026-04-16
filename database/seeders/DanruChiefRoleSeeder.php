<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DanruChiefRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan permission yang dibutuhkan sudah ada
        $needed = [
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'absensi.view-unit',
            'jadwal.view-self',
            'jadwal.view-unit',
            'jadwal.manage',
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.approve',
        ];
        foreach ($needed as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Role danru
        $danru = Role::firstOrCreate(['name' => 'danru']);
        $danru->syncPermissions([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'absensi.view-unit',
            'jadwal.view-self',
            'jadwal.view-unit',
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.approve',
        ]);

        // Role chief
        $chief = Role::firstOrCreate(['name' => 'chief']);
        $chief->syncPermissions([
            'dashboard.view',
            'pjlp.view-self',
            'absensi.view-self',
            'absensi.view-unit',
            'jadwal.view-self',
            'jadwal.view-unit',
            'jadwal.manage',
            'cuti.create',
            'cuti.view-self',
            'cuti.view-unit',
            'cuti.approve',
        ]);

        // Revisi manajemen: hanya rekap absensi + rekap cuti
        $manajemen = Role::findByName('manajemen');
        if ($manajemen) {
            $manajemen->syncPermissions([
                'dashboard.view',
                'absensi.view-all',
                'cuti.view-all',
            ]);
        }

        $this->command->info('Role danru, chief berhasil dibuat. Role manajemen direvisi.');
    }
}
