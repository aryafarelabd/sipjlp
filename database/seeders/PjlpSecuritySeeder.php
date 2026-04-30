<?php

namespace Database\Seeders;

use App\Enums\StatusPjlp;
use App\Enums\UnitType;
use App\Models\Pjlp;
use Illuminate\Database\Seeder;

class PjlpSecuritySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nip' => '80341897',   'nama' => 'Yudi Kurniawan Bin Suroso',  'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19920219'],
            ['nip' => '80346096',   'nama' => 'Selamet',                    'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19780623'],
            ['nip' => '80243566',   'nama' => 'Badri',                      'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19870130'],
            ['nip' => '80341979',   'nama' => 'Achmad Faizal',              'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19780613'],
            ['nip' => '80225057',   'nama' => 'Yuyun Yuniarsih',            'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19830617'],
            ['nip' => '80016530',   'nama' => 'Agnasihan',                  'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19840730'],
            ['nip' => '80225056',   'nama' => 'Nining Sriyanti',            'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19820504'],
            ['nip' => '80553241',   'nama' => 'Irfai Fajar Nuridwan',       'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19950422'],
            ['nip' => '80241848',   'nama' => 'Budi Santoso',               'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19760902'],
            ['nip' => '80346095',   'nama' => 'Liftahudin',                 'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19970821'],
            ['nip' => '80553393',   'nama' => 'Fajar Rudin',                'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '1982013'],
            ['nip' => '80553167',   'nama' => 'Kusri Handayani',            'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19810824'],
            ['nip' => '80341976',   'nama' => 'Yoan Agustilasera',          'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19830811'],
            ['nip' => '80247811',   'nama' => 'Murjadi',                    'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19751231'],
            ['nip' => '80241852',   'nama' => 'Herlina',                    'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19780922'],
            ['nip' => '80341971',   'nama' => 'Leman',                      'tanggal_bergabung' => '2022-01-01', 'jabatan' => 'Security',    'badge_number' => '19800712'],
            ['nip' => '1994072602', 'nama' => 'Dimas Pringga Pratama',      'tanggal_bergabung' => '2024-01-01', 'jabatan' => 'Security',    'badge_number' => '19940726'],
            ['nip' => '2002010302', 'nama' => 'Muhamad Wardani',            'tanggal_bergabung' => '2025-01-01', 'jabatan' => 'Security',    'badge_number' => '20020103'],
            ['nip' => '1997061902', 'nama' => 'Agung Aziz Prasetyo Adi',   'tanggal_bergabung' => '2025-01-01', 'jabatan' => 'Security',    'badge_number' => '19970619'],
            ['nip' => '1987112102', 'nama' => 'Abdul Rohman',               'tanggal_bergabung' => '2025-01-01', 'jabatan' => 'Security',    'badge_number' => '19871121'],
            ['nip' => '2003022402', 'nama' => 'Dhafa Putra Mahendra',       'tanggal_bergabung' => '2025-01-01', 'jabatan' => 'Security',    'badge_number' => '20030224'],
            ['nip' => '1995051902', 'nama' => 'Fikri Aliansyah',            'tanggal_bergabung' => '2026-01-02', 'jabatan' => 'Security',    'badge_number' => '19950519'],
            ['nip' => '1999091402', 'nama' => 'Fachrizal Fladi Pratama',    'tanggal_bergabung' => '2026-01-02', 'jabatan' => 'Security',    'badge_number' => '19990914'],
            ['nip' => '19980323',   'nama' => 'Nugroho Bagus Maryanto',     'tanggal_bergabung' => '2023-01-01', 'jabatan' => 'PJ Security', 'badge_number' => '19980323'],
        ];

        foreach ($data as $row) {
            Pjlp::updateOrCreate(
                ['nip' => $row['nip']],
                [
                    'nama'              => $row['nama'],
                    'unit'              => UnitType::SECURITY,
                    'jabatan'           => $row['jabatan'],
                    'tanggal_bergabung' => $row['tanggal_bergabung'],
                    'badge_number'      => $row['badge_number'],
                    'status'            => StatusPjlp::AKTIF,
                ]
            );
        }

        $this->command->info('Berhasil menambahkan ' . count($data) . ' pegawai Security.');
    }
}
