<?php

namespace Database\Seeders;

use App\Enums\StatusPjlp;
use App\Enums\UnitType;
use App\Models\Pjlp;
use Illuminate\Database\Seeder;

class PjlpCleaningServiceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nip' => '80341887',   'nama' => 'Mutia Rahayu',                      'tanggal_bergabung' => '2022-01-01', 'badge_number' => '199610021'],
            ['nip' => '80346071',   'nama' => 'Sidik Kurniawan',                   'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19981114'],
            ['nip' => '80341886',   'nama' => 'Humairoh',                          'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19981226'],
            ['nip' => '80241837',   'nama' => 'Andika Putra Pratama',              'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19980504'],
            ['nip' => '80241839',   'nama' => 'Hendi Rahmad Prasetyo',             'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19980903'],
            ['nip' => '80243564',   'nama' => 'Saari Amri',                        'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19951219'],
            ['nip' => '80347100',   'nama' => 'Peni Fitri Alycia',                 'tanggal_bergabung' => '2022-01-01', 'badge_number' => '20001023'],
            ['nip' => '80241664',   'nama' => 'Urip Widiansyah',                   'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19900523'],
            ['nip' => '80112910',   'nama' => 'Afifah Fitriani',                   'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19960415'],
            ['nip' => '80346083',   'nama' => 'Supriyatini',                       'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19800710'],
            ['nip' => '80346063',   'nama' => 'Chaerul Aznur Salam',              'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19990817'],
            ['nip' => '80346061',   'nama' => 'Ade Irvandi',                       'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19950922'],
            ['nip' => '80241838',   'nama' => 'Andri Riyanto',                     'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19880930'],
            ['nip' => '80111328',   'nama' => 'Muntofi\'ah',                       'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19750320'],
            ['nip' => '80341890',   'nama' => 'Ratno',                             'tanggal_bergabung' => '2022-01-01', 'badge_number' => '19760908'],
            ['nip' => '1996090501', 'nama' => 'Yudha Febiansyah',                  'tanggal_bergabung' => '2024-02-01', 'badge_number' => '1996095'],
            ['nip' => '1992022101', 'nama' => 'Darmawanto',                        'tanggal_bergabung' => '2022-09-01', 'badge_number' => '19920221'],
            ['nip' => '2002021501', 'nama' => 'Febrian Yanwar Hanan',              'tanggal_bergabung' => '2022-11-01', 'badge_number' => '20021502'],
            ['nip' => '2002032401', 'nama' => 'Muhammad Rizal Zazuli',             'tanggal_bergabung' => '2022-11-01', 'badge_number' => '20020324'],
            ['nip' => '2004051101', 'nama' => 'Mutia Dwi Dini Maulida',            'tanggal_bergabung' => '2023-07-06', 'badge_number' => '20040511'],
            ['nip' => '2004040401', 'nama' => 'Ardiansyah Bekti Wahyu Nugroho',    'tanggal_bergabung' => '2023-07-06', 'badge_number' => '20040404'],
            ['nip' => '2000042201', 'nama' => 'Arfiansyah',                        'tanggal_bergabung' => '2022-11-01', 'badge_number' => '20000422'],
            ['nip' => '1995072301', 'nama' => 'Annas Tiara Pamungkass',            'tanggal_bergabung' => '2025-01-01', 'badge_number' => '19950723'],
            ['nip' => '1996101701', 'nama' => 'Nuur Zaky Arifuddin Rahman',        'tanggal_bergabung' => '2026-01-12', 'badge_number' => '19961017'],
        ];

        foreach ($data as $row) {
            Pjlp::updateOrCreate(
                ['nip' => $row['nip']],
                [
                    'nama'             => $row['nama'],
                    'unit'             => UnitType::CLEANING,
                    'jabatan'          => 'Cleaning Service',
                    'tanggal_bergabung'=> $row['tanggal_bergabung'],
                    'badge_number'     => $row['badge_number'],
                    'status'           => StatusPjlp::AKTIF,
                ]
            );
        }

        $this->command->info('Berhasil menambahkan ' . count($data) . ' pegawai Cleaning Service.');
    }
}
