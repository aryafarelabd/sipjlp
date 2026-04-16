<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_hepafilter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->date('tanggal');

            // Ruangan yang dibersihkan (boolean per ruangan)
            $table->boolean('ruang_poli_gigi')->default(false);
            $table->boolean('ruang_poli_paru')->default(false);
            $table->boolean('ruang_igd_isolasi')->default(false);
            $table->boolean('ruang_perina')->default(false);
            $table->boolean('ruang_cssd')->default(false);
            $table->boolean('ruang_bayanaka')->default(false);
            $table->boolean('ruang_kaivan')->default(false);
            $table->boolean('ruang_elektromedis')->default(false);
            $table->boolean('rumah_dinas')->default(false);

            $table->string('catatan', 500)->nullable();
            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
        });

        Schema::create('logbook_hepafilter_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_hepafilter_id')->constrained('logbook_hepafilter')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_hepafilter_foto');
        Schema::dropIfExists('logbook_hepafilter');
    }
};
