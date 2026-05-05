<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->enum('status', ['normal', 'libur', 'libur_hari_raya', 'cuti', 'izin', 'sakit', 'alpha'])
                  ->default('normal')
                  ->after('lokasi_id');
            $table->foreignId('shift_id')->nullable()->change();
            $table->foreignId('lokasi_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
