<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengawasan_proyek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('nama_proyek');
            $table->string('lokasi');
            $table->json('lalu_lalang')->nullable();
            $table->json('apd')->nullable();
            $table->json('penanganan_udara')->nullable();
            $table->json('sampah_puing')->nullable();
            $table->json('area_proyek')->nullable();
            $table->json('kepatuhan_b3')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengawasan_proyek');
    }
};
