<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_inspeksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->date('tanggal');
            $table->string('area'); // lantai_1, lantai_2, dll

            // Seksi inspeksi (JSON per seksi)
            $table->json('tangga_darurat')->nullable();
            $table->json('ramp_evakuasi')->nullable();
            $table->json('koridor_lantai')->nullable();
            $table->json('pencahayaan')->nullable();
            $table->json('jendela')->nullable();
            $table->json('penyimpanan_berkas')->nullable();
            $table->json('ruang_kantor')->nullable();
            $table->json('pembuangan_sampah')->nullable();
            $table->json('tabung_oksigen')->nullable();
            $table->json('bahan_berbahaya')->nullable();
            $table->json('bahaya_listrik')->nullable();
            $table->json('toilet')->nullable();
            $table->json('papan_codered')->nullable();

            // Pembatasan akses malam
            $table->json('pintu_akses')->nullable();   // {pintu_samping_rm: true, ...}
            $table->text('alasan_pintu')->nullable();

            // Penutup
            $table->text('rekomendasi');

            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
        });

        Schema::create('patrol_inspeksi_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_inspeksi_id')->constrained('patrol_inspeksi')->cascadeOnDelete();
            $table->string('seksi'); // tangga_darurat, ramp_evakuasi, ..., temuan
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_inspeksi_foto');
        Schema::dropIfExists('patrol_inspeksi');
    }
};
