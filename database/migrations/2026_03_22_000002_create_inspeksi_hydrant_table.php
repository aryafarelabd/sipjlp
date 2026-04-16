<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeksi_hydrant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->date('tanggal');

            // 5 lokasi hydrant (JSON per lokasi)
            $table->json('siamesse_igd')->nullable();
            $table->json('utilitas_1')->nullable();
            $table->json('utilitas_2')->nullable();
            $table->json('parkir_motor')->nullable();
            $table->json('gardu_pln')->nullable();

            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_hydrant');
    }
};
