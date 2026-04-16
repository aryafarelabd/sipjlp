<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeksi_hydrant_indoor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('lokasi'); // lantai_1_gizi | lantai_2 | lantai_3 | lantai_4
            $table->json('hydrant_1')->nullable(); // {nozzle, selang, selang_ket, box, box_ket, alarm, hose_rack, keterangan}
            $table->json('hydrant_2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_hydrant_indoor');
    }
};
