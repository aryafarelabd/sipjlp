<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kecelakaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_pelapor');
            $table->string('unit_bagian');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('tempat');
            $table->text('saksi');
            // Data korban
            $table->unsignedTinyInteger('jumlah_laki')->default(0);
            $table->unsignedTinyInteger('jumlah_perempuan')->default(0);
            $table->text('nama_korban');
            $table->string('umur_korban');
            $table->unsignedTinyInteger('akibat_mati')->default(0);
            $table->unsignedTinyInteger('akibat_luka_berat')->default(0);
            $table->unsignedTinyInteger('akibat_luka_ringan')->default(0);
            $table->text('keterangan_cedera');
            // Fakta
            $table->text('kondisi_berbahaya');
            $table->text('tindakan_berbahaya');
            $table->text('uraian_kejadian');
            $table->text('sumber_kejadian');
            // Tipe
            $table->string('tipe'); // accident | incident | near_miss
            // File
            $table->string('foto_bukti')->nullable();
            $table->string('file_formulir')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kecelakaan');
    }
};
