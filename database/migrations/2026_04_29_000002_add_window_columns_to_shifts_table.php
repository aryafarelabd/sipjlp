<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('bi')->nullable()->after('jam_mulai')->comment('Batas bawah window deteksi jam masuk');
            $table->time('ai')->nullable()->after('bi')->comment('Batas atas window deteksi jam masuk');
            $table->time('bo')->nullable()->after('jam_selesai')->comment('Batas bawah window deteksi jam pulang');
            $table->time('ao')->nullable()->after('bo')->comment('Batas atas window deteksi jam pulang');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['bi', 'ai', 'bo', 'ao']);
        });
    }
};
