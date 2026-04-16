<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lembar Kerja CS - Header lembar kerja harian per PJLP
     * Setiap PJLP CS mengisi lembar kerja harian berdasarkan area dan shift yang ditugaskan
     */
    public function up(): void
    {
        Schema::create('lembar_kerja_limbah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('master_area_cs')->onDelete('cascade');
            $table->date('tanggal');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->enum('kategori_limbah', ['organik', 'anorganik', 'kompos'])->nullable();
    
            

            // Status workflow
            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])->default('draft');

            // Catatan
            $table->text('catatan_pjlp')->nullable()->comment('Catatan dari PJLP');
            $table->text('catatan_koordinator')->nullable()->comment('Catatan dari Koordinator');

            // Timestamps workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Unique: 1 lembar kerja per PJLP per area per tanggal per shift
            $table->unique(['pjlp_id', 'area_id', 'tanggal', 'shift_id'], 'lembar_kerja_limbah_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembar_kerja_limbah');
    }
};
