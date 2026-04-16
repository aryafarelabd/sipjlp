<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengecekan_apar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('lokasi'); // lantai_1 | lantai_2 | lantai_3 | lantai_4_rooftop | luar_gedung
            $table->json('units');   // {unit_key: 'baik'|'buruk'}
            $table->text('keterangan_buruk')->nullable();
            // Rincian pemeriksaan
            $table->string('berat')->nullable();     // e.g. "6 kg"
            $table->string('tekanan')->nullable();   // M (aman) / H (tidak aman)
            $table->string('kondisi');               // baik | buruk
            $table->text('kondisi_ket')->nullable();
            $table->string('pin_segel');             // ada | tidak
            $table->string('handle');                // baik | buruk
            $table->string('petunjuk');              // ada | tidak
            $table->string('segitiga_api');          // ada | tidak
            $table->date('masa_berlaku');
            $table->text('keterangan_lain')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengecekan_apar');
    }
};
