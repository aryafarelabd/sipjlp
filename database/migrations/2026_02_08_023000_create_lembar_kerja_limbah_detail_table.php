<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembar_kerja_limbah_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembar_kerja_id')->constrained('lembar_kerja_limbah')->onDelete('cascade');
            $table->foreignId('pekerjaan_id')->nullable()->constrained('master_logbooks')->onDelete('set null');
            $table->boolean('is_completed')->default(false);
            $table->string('foto_bukti')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('dikerjakan_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembar_kerja_limbah_detail');
    }
};
