<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus detail/child tables dulu sebelum parent
        Schema::dropIfExists('lembar_kerja_limbah_detail');
        Schema::dropIfExists('lembar_kerja_detail');
        Schema::dropIfExists('lembar_kerja_limbah');
        Schema::dropIfExists('lembar_kerja_validasi');
        Schema::dropIfExists('lembar_kerja');
        Schema::dropIfExists('bukti_pekerjaan_limbah');
        Schema::dropIfExists('limbah_domestik');
        Schema::dropIfExists('limbah_medis');
        Schema::dropIfExists('logbook_limbahs');
        Schema::dropIfExists('absensi_raw');
    }

    public function down(): void
    {
        // Tidak di-restore — tabel ini memang tidak dipakai lagi
    }
};
