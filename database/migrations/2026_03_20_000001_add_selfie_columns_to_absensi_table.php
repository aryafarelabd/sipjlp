<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->string('foto_masuk')->nullable()->after('keterangan');
            $table->string('foto_pulang')->nullable()->after('foto_masuk');
            $table->decimal('latitude_masuk', 10, 7)->nullable()->after('foto_pulang');
            $table->decimal('longitude_masuk', 10, 7)->nullable()->after('latitude_masuk');
            $table->decimal('latitude_pulang', 10, 7)->nullable()->after('longitude_masuk');
            $table->decimal('longitude_pulang', 10, 7)->nullable()->after('latitude_pulang');
        });

        // Extend sumber_data enum to include 'selfie'
        DB::statement("ALTER TABLE absensi MODIFY COLUMN sumber_data ENUM('mesin','manual','selfie') NOT NULL DEFAULT 'mesin'");
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn([
                'foto_masuk',
                'foto_pulang',
                'latitude_masuk',
                'longitude_masuk',
                'latitude_pulang',
                'longitude_pulang',
            ]);
        });

        DB::statement("ALTER TABLE absensi MODIFY COLUMN sumber_data ENUM('mesin','manual') NOT NULL DEFAULT 'mesin'");
    }
};
