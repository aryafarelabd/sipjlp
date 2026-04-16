<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek dulu biar nggak bentrok kalau tabelnya udah ada
        if (!Schema::hasTable('master_logbooks')) {
            Schema::create('master_logbooks', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kode')->nullable();
                $table->string('kategori'); 
                $table->text('deskripsi')->nullable();
                $table->integer('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // BAGIAN JADWAL CS BULANAN DIHAPUS DARI SINI
    }

    public function down(): void
    {
        Schema::dropIfExists('master_logbooks');
    }
};