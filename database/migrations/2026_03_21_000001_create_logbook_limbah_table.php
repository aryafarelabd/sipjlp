<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_limbah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pjlp_id')->constrained('pjlp')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('master_area_cs')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('berat_domestik', 8, 2)->default(0)->comment('kg');
            $table->decimal('berat_kompos', 8, 2)->default(0)->comment('kg');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['pjlp_id', 'tanggal']);
            $table->index(['area_id', 'tanggal']);
        });

        Schema::create('logbook_limbah_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbook_limbah')->cascadeOnDelete();
            $table->enum('kategori', ['apd', 'timbangan']);
            $table->string('path');
            $table->timestamps();

            $table->index(['logbook_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_limbah_foto');
        Schema::dropIfExists('logbook_limbah');
    }
};
