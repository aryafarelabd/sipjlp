<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel lama
        Schema::dropIfExists('lembar_kerja_cs_detail');
        Schema::dropIfExists('lembar_kerja_cs');

        // Master Kegiatan Lembar Kerja CS
        Schema::create('master_kegiatan_lk_cs', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200);
            $table->enum('tipe', ['periodik', 'extra_job']);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Lembar Kerja CS — konsep baru
        Schema::create('lembar_kerja_cs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('master_area_cs')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('tanggal');
            $table->json('kegiatan_periodik')->comment('Array of {id, nama}');
            $table->json('kegiatan_extra_job')->nullable()->comment('Array of {id, nama}');
            $table->json('foto_dokumentasi')->nullable()->comment('Array of file paths, max 20');
            $table->text('deskripsi_foto')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('catatan_koordinator')->nullable();
            $table->timestamps();

            $table->unique(['pjlp_id', 'tanggal', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembar_kerja_cs');
        Schema::dropIfExists('master_kegiatan_lk_cs');
    }
};
