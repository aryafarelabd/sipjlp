<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_dekontaminasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->date('tanggal');
            $table->text('lokasi'); // free text ruangan
            $table->string('catatan', 500)->nullable();
            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
        });

        Schema::create('logbook_dekontaminasi_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_dekontaminasi_id')->constrained('logbook_dekontaminasi')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_dekontaminasi_foto');
        Schema::dropIfExists('logbook_dekontaminasi');
    }
};
