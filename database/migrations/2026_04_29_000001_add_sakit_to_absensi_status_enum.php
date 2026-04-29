<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','terlambat','alpha','izin','cuti','sakit','libur') NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','terlambat','alpha','izin','cuti','libur') NOT NULL DEFAULT 'hadir'");
    }
};
