<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // absensi — hot path: query harian & rekap bulanan
        Schema::table('absensi', function (Blueprint $table) {
            $table->index(['pjlp_id', 'tanggal'],  'idx_absensi_pjlp_tanggal');
            $table->index(['pjlp_id', 'status'],   'idx_absensi_pjlp_status');
            $table->index(['tanggal', 'status'],    'idx_absensi_tanggal_status');
        });

        // jadwal (security) — query harian di AbsensiSelfieService
        Schema::table('jadwal', function (Blueprint $table) {
            $table->index(['pjlp_id', 'tanggal'],          'idx_jadwal_pjlp_tanggal');
            $table->index(['tanggal', 'is_published'],     'idx_jadwal_tanggal_published');
        });

        // jadwal_shift_cs — query harian di AbsensiSelfieService
        Schema::table('jadwal_shift_cs', function (Blueprint $table) {
            $table->index(['pjlp_id', 'tanggal'],  'idx_jsc_pjlp_tanggal');
            $table->index(['tanggal', 'status'],   'idx_jsc_tanggal_status');
        });

        // cuti — approval workflow
        Schema::table('cuti', function (Blueprint $table) {
            $table->index(['pjlp_id', 'status'],   'idx_cuti_pjlp_status');
        });

        // lembar_kerja_cs — filter workflow
        Schema::table('lembar_kerja_cs', function (Blueprint $table) {
            $table->index(['pjlp_id', 'status'],   'idx_lkcs_pjlp_status');
            $table->index('status',                'idx_lkcs_status');
        });

        // lembar_kerja_security — filter workflow
        Schema::table('lembar_kerja_security', function (Blueprint $table) {
            $table->index('status',                'idx_lks_status');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_pjlp_tanggal');
            $table->dropIndex('idx_absensi_pjlp_status');
            $table->dropIndex('idx_absensi_tanggal_status');
        });

        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_pjlp_tanggal');
            $table->dropIndex('idx_jadwal_tanggal_published');
        });

        Schema::table('jadwal_shift_cs', function (Blueprint $table) {
            $table->dropIndex('idx_jsc_pjlp_tanggal');
            $table->dropIndex('idx_jsc_tanggal_status');
        });

        Schema::table('cuti', function (Blueprint $table) {
            $table->dropIndex('idx_cuti_pjlp_status');
        });

        Schema::table('lembar_kerja_cs', function (Blueprint $table) {
            $table->dropIndex('idx_lkcs_pjlp_status');
            $table->dropIndex('idx_lkcs_status');
        });

        Schema::table('lembar_kerja_security', function (Blueprint $table) {
            $table->dropIndex('idx_lks_status');
        });
    }
};
