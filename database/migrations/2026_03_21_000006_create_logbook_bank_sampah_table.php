<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_bank_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->date('tanggal');

            // Jenis sampah (kg, semua nullable)
            $table->decimal('kardus', 8, 2)->nullable();
            $table->decimal('jerigen_besar', 8, 2)->nullable();
            $table->decimal('jerigen_kecil', 8, 2)->nullable();
            $table->decimal('botol', 8, 2)->nullable();
            $table->decimal('plastik', 8, 2)->nullable();
            $table->decimal('baja', 8, 2)->nullable();
            $table->decimal('paralon', 8, 2)->nullable();
            $table->decimal('diplek', 8, 2)->nullable();
            $table->decimal('kertas', 8, 2)->nullable();
            $table->decimal('besi', 8, 2)->nullable();
            $table->decimal('seng', 8, 2)->nullable();

            $table->string('catatan', 500)->nullable();
            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
        });

        Schema::create('logbook_bank_sampah_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_bank_sampah_id')->constrained('logbook_bank_sampah')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_bank_sampah_foto');
        Schema::dropIfExists('logbook_bank_sampah');
    }
};
