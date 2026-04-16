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
            $table->unsignedBigInteger('danru_id')->nullable()->after('pjlp_id');
            $table->foreign('danru_id')->references('id')->on('pjlp')->nullOnDelete();

            $table->unsignedBigInteger('approved_by_danru')->nullable()->after('approved_at');
            $table->foreign('approved_by_danru')->references('id')->on('users')->nullOnDelete();
            $table->datetime('approved_at_danru')->nullable()->after('approved_by_danru');

            $table->unsignedBigInteger('approved_by_chief')->nullable()->after('approved_at_danru');
            $table->foreign('approved_by_chief')->references('id')->on('users')->nullOnDelete();
            $table->datetime('approved_at_chief')->nullable()->after('approved_by_chief');
        });

        // Expand enum status — tambah nilai berjenjang
        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM(
            'menunggu',
            'menunggu_danru',
            'menunggu_chief',
            'menunggu_koordinator',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['danru_id']);
            $table->dropForeign(['approved_by_danru']);
            $table->dropForeign(['approved_by_chief']);
            $table->dropColumn(['danru_id', 'approved_by_danru', 'approved_at_danru', 'approved_by_chief', 'approved_at_chief']);
        });

        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu'");
    }
};
