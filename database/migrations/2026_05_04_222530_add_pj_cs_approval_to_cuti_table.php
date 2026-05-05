<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by_pj_cs')->nullable()->after('approved_at_chief');
            $table->foreign('approved_by_pj_cs')->references('id')->on('users')->nullOnDelete();
            $table->datetime('approved_at_pj_cs')->nullable()->after('approved_by_pj_cs');
        });

        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM(
            'menunggu',
            'menunggu_danru',
            'menunggu_chief',
            'menunggu_koordinator',
            'menunggu_pj_cs',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['approved_by_pj_cs']);
            $table->dropColumn(['approved_by_pj_cs', 'approved_at_pj_cs']);
        });

        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM(
            'menunggu',
            'menunggu_danru',
            'menunggu_chief',
            'menunggu_koordinator',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'menunggu'");
    }
};
