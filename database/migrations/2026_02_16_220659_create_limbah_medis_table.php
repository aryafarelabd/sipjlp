<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('limbah_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->nullable()->constrained('pjlp')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('master_area_cs')->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('pekerjaan_id')->nullable()->constrained('master_logbooks')->onDelete('set null');
            $table->date('tanggal');
            $table->string('pekerjaan')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('tipe_limbah', ['domestik', 'medis'])->default('medis');
            $table->enum('jenis_limbah', ['infeksius', 'B3', 'kompos'])->nullable();
            $table->string('kategori_limbah', 100)->nullable();
            $table->decimal('jumlah_kg', 8, 2)->default(0);
            $table->decimal('berat', 8, 2)->nullable();
            $table->string('foto_bukti')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);

            $table->boolean('is_validated')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limbah_medis');
    }
};

