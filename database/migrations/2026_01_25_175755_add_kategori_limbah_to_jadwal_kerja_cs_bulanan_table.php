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
        // Cek apakah kolom 'kategori' sudah ada di tabel 'master_pekerjaan_cs'
        if (!Schema::hasColumn('master_pekerjaan_cs', 'kategori')) {
            Schema::table('master_pekerjaan_cs', function (Blueprint $table) {
                $table->string('kategori')->default('cleaning')->after('nama');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback harus menghapus kolom yang dibuat di method up()
        if (Schema::hasColumn('master_pekerjaan_cs', 'kategori')) {
            Schema::table('master_pekerjaan_cs', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }
};