<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('logbook_limbahs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users'); // Petugas yang input
        $table->foreignId('area_id')->constrained('master_area_cs');
        $table->date('tanggal');
        $table->foreignId('shift_id')->constrained('shifts');
        $table->foreignId('pekerjaan_id')->nullable()->constrained('master_pekerjaan_cs');
        
        // Parameter Khusus Limbah
        $table->enum('tipe_limbah', ['domestik', 'medis']);
        $table->string('kategori_limbah'); // Infeksius, Tajam, Organik, dll
        $table->decimal('berat', 8, 2)->nullable(); // Khusus Medis biasanya pakai Kg
        $table->integer('jumlah_kantong')->nullable(); // Khusus Domestik
        
        $table->text('keterangan')->nullable();
        $table->string('foto_bukti')->nullable(); // Untuk validasi fisik
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_limbahs');
    }
};
