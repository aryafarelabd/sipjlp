<?php

namespace App\Console\Commands;

use App\Services\TarikAbsenMesinService;
use Illuminate\Console\Command;

class TarikAbsenMesin extends Command
{
    protected $signature = 'absen:tarik
                            {--tanggal= : Tanggal spesifik (format: Y-m-d). Default: semua data tahun ini}
                            {--hanya-tarik : Hanya tarik dari NPIS ke log_absensi_mesin, tidak proses ke absensi}
                            {--hanya-proses : Hanya proses log_absensi_mesin yang sudah ada ke tabel absensi}';

    protected $description = 'Tarik data absensi dari DB NPIS dan proses ke tabel absensi SIPJLP';

    public function __construct(protected TarikAbsenMesinService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tanggal     = $this->option('tanggal');
        $hanyaTarik  = $this->option('hanya-tarik');
        $hanyaProses = $this->option('hanya-proses');

        if ($tanggal && !\Carbon\Carbon::canBeCreatedFromFormat($tanggal, 'Y-m-d')) {
            $this->error('Format tanggal tidak valid. Gunakan format Y-m-d, contoh: 2026-04-29');
            return self::FAILURE;
        }

        // Step 1: Tarik dari NPIS
        if (!$hanyaProses) {
            $this->info('Menarik data dari DB NPIS...');

            try {
                $hasil = $this->service->tarikDariNpis($tanggal);
                $this->info("  ✓ Ditarik  : {$hasil['pulled']} record");
                $this->line("  - Dilewati : {$hasil['skipped']} record (sudah ada)");
            } catch (\Exception $e) {
                $this->error('Gagal konek ke DB NPIS: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        if ($hanyaTarik) {
            $this->info('Selesai (hanya tarik).');
            return self::SUCCESS;
        }

        // Step 2: Proses ke tabel absensi
        $this->info('Memproses ke tabel absensi...');
        $hasil = $this->service->prosesKeAbsensi($tanggal);
        $this->info("  ✓ Diproses : {$hasil['processed']} PJLP/hari");
        $this->line("  - Dilewati : {$hasil['skipped']} (tidak ada jadwal / data tidak lengkap)");

        $this->info('Selesai.');
        return self::SUCCESS;
    }
}
