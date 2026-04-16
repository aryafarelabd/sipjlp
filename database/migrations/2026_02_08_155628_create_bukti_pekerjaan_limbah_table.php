<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bukti Pekerjaan Limbah - Diinput oleh PJLP
     * Berisi foto bukti, berat limbah (Kg), dan timestamp pengerjaan
     */
    public function up(): void
    {
        // Nama tabel dibedakan agar data Limbah terpisah dari CS
        Schema::create('bukti_pekerjaan_limbah', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke jadwal (Tetap ke jadwal_kerja_cs_bulanan jika jadwalnya memang gabung)
            $table->foreignId('jadwal_bulanan_id')->constrained('jadwal_kerja_cs_bulanan')->onDelete('cascade');
            
            $table->foreignId('pjlp_id')->nullable()->constrained('pjlp')->onDelete('set null');
            $table->string('foto_bukti')->comment('Path foto bukti pekerjaan');
            
            // --- KOLOM KHUSUS LIMBAH (Agar bisa simpan data Kg) ---
            $table->decimal('jumlah_kg', 10, 2)->default(0)->comment('Input berat limbah dalam Kg');
             $table->string('kategori_limbah')->nullable();
            
            $table->text('catatan')->nullable()->comment('Catatan dari PJLP');
            $table->timestamp('dikerjakan_at')->comment('Waktu upload bukti (otomatis)');
            $table->boolean('is_completed')->default(true);

            // Validasi oleh Koordinator (Sama persis dengan CS)
            $table->boolean('is_validated')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->text('catatan_validator')->nullable();

            $table->timestamps();

            // Index agar query cepat
            $table->index(['jadwal_bulanan_id', 'is_completed']);
            $table->index(['pjlp_id', 'dikerjakan_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_pekerjaan_limbah');
    }
};