<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_b3', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('master_area_cs')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->date('tanggal');

            // Plastik Kuning — Limbah Padat Infeksius (per lokasi, nullable)
            $table->decimal('pk_lt1',       8, 2)->nullable()->comment('Plastik Kuning Lt.1 (kg)');
            $table->decimal('pk_igd',        8, 2)->nullable()->comment('Plastik Kuning IGD (kg)');
            $table->decimal('pk_lt2',        8, 2)->nullable()->comment('Plastik Kuning Lt.2 (kg)');
            $table->decimal('pk_ok',         8, 2)->nullable()->comment('Plastik Kuning OK (kg)');
            $table->decimal('pk_lt3',        8, 2)->nullable()->comment('Plastik Kuning Lt.3 (kg)');
            $table->decimal('pk_lt4',        8, 2)->nullable()->comment('Plastik Kuning Lt.4 (kg)');
            $table->decimal('pk_utilitas',   8, 2)->nullable()->comment('Plastik Kuning Utilitas (kg)');
            $table->decimal('pk_taman',      8, 2)->nullable()->comment('Plastik Kuning Taman/Halaman (kg)');

            // Safety Box — Limbah Tajam Infeksius
            $table->string('safety_box_asal')->nullable();
            $table->decimal('safety_box_kg', 8, 2)->nullable();

            // Limbah Cair Infeksius
            $table->string('cair_asal')->nullable();
            $table->decimal('cair_kg', 8, 2)->nullable();

            // Hepafilter
            $table->string('hepafilter_asal')->nullable();
            $table->decimal('hepafilter_kg', 8, 2)->nullable();

            // Non Infeksius
            $table->string('non_infeksius_jenis')->nullable();
            $table->decimal('non_infeksius_kg', 8, 2)->nullable();

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
            $table->index(['area_id', 'tanggal']);
        });

        Schema::create('logbook_b3_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_b3_id')->constrained('logbook_b3')->cascadeOnDelete();
            $table->enum('kategori', ['apd', 'timbangan']);
            $table->string('path');
            $table->timestamps();

            $table->index(['logbook_b3_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_b3_foto');
        Schema::dropIfExists('logbook_b3');
    }
};
