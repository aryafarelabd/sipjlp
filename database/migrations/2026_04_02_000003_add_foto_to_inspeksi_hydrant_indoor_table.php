<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspeksi_hydrant_indoor', function (Blueprint $table) {
            $table->json('foto_hydrant_1')->nullable()->after('hydrant_2');
            $table->json('foto_hydrant_2')->nullable()->after('foto_hydrant_1');
        });
    }

    public function down(): void
    {
        Schema::table('inspeksi_hydrant_indoor', function (Blueprint $table) {
            $table->dropColumn(['foto_hydrant_1', 'foto_hydrant_2']);
        });
    }
};
