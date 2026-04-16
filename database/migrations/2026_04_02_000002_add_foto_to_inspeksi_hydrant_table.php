<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspeksi_hydrant', function (Blueprint $table) {
            $table->json('foto_siamesse_igd')->nullable()->after('gardu_pln');
            $table->json('foto_utilitas_1')->nullable()->after('foto_siamesse_igd');
            $table->json('foto_utilitas_2')->nullable()->after('foto_utilitas_1');
            $table->json('foto_parkir_motor')->nullable()->after('foto_utilitas_2');
            $table->json('foto_gardu_pln')->nullable()->after('foto_parkir_motor');
        });
    }

    public function down(): void
    {
        Schema::table('inspeksi_hydrant', function (Blueprint $table) {
            $table->dropColumn([
                'foto_siamesse_igd',
                'foto_utilitas_1',
                'foto_utilitas_2',
                'foto_parkir_motor',
                'foto_gardu_pln',
            ]);
        });
    }
};
