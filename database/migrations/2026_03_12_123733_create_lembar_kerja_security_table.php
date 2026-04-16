<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Kita benerin tujuannya ke tabel yang sudah ada di phpMyAdmin lu
        Schema::create('lembar_kerja_security', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            
            // Lu liat di phpMyAdmin tadi namanya master_area_cs kan? 
            // Kita tembak ke sana biar dia gak error lagi.
            $table->foreignId('area_id')->constrained('master_area_cs'); 
            
            $table->foreignId('pjlp_id')->constrained('pjlp');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->string('status')->default('draft');
            $table->text('catatan_pjlp')->nullable();
            $table->text('catatan_validator')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lembar_kerja_security_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembar_kerja_id')->constrained('lembar_kerja_security')->onDelete('cascade');
            
            // Ini juga kita tembak ke master_aktivitas_cs sesuai screenshot lu
            $table->foreignId('aktivitas_id')->constrained('master_aktivitas_cs');
            
            $table->boolean('is_completed')->default(false);
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_before')->nullable();
            $table->string('foto_after')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lembar_kerja_security_detail');
        Schema::dropIfExists('lembar_kerja_security');
    }
};