<?php

namespace App\Console\Commands;

use App\Services\TarikAbsenMesinService;
use Illuminate\Console\Command;

class TarikAbsenMesin extends Command
{
    protected $signature = 'absen:tarik
                            {--tanggal= : Tanggal spesifik (format: Y-m-d). Default: semua data tahun ini}
                            {--bulan= : Bulan spesifik (1-12), wajib dipakai bersama/atau otomatis tahun berjalan}
                            {--tahun= : Tahun untuk opsi --bulan. Default: tahun berjalan}
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
        $bulan       = $this->option('bulan');
        $tahun       = $this->option('tahun') ?: now()->year;
        $hanyaTarik  = $this->option('hanya-tarik');
        $hanyaProses = $this->option('hanya-proses');

        if ($tanggal && $bulan) {
            $this->error('Gunakan salah satu: --tanggal atau --bulan, jangan keduanya.');
            return self::FAILURE;
        }

        if ($tanggal && !\Carbon\Carbon::canBeCreatedFromFormat($tanggal, 'Y-m-d')) {
            $this->error('Format tanggal tidak valid. Gunakan format Y-m-d, contoh: 2026-04-29');
            return self::FAILURE;
        }

        if ($bulan) {
            $bulan = (int) $bulan;
            $tahun = (int) $tahun;

            if ($bulan < 1 || $bulan > 12) {
                $this->error('Bulan tidak valid. Gunakan angka 1 sampai 12.');
                return self::FAILURE;
            }

            if ($tahun < 2000 || $tahun > 2100) {
                $this->error('Tahun tidak valid.');
                return self::FAILURE;
            }

            return $this->handleBulan($bulan, $tahun, $hanyaTarik, $hanyaProses);
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

    private function handleBulan(int $bulan, int $tahun, bool $hanyaTarik, bool $hanyaProses): int
    {
        $start = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $this->info('Menarik absensi bulan ' . $start->translatedFormat('F Y') . '...');

        $totalPulled = 0;
        $totalSkippedPull = 0;
        $totalProcessed = 0;
        $totalSkippedProcess = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tanggal = $date->toDateString();
            $this->line("Tanggal {$tanggal}");

            if (!$hanyaProses) {
                try {
                    $hasilTarik = $this->service->tarikDariNpis($tanggal);
                } catch (\Exception $e) {
                    $this->error('Gagal konek ke DB NPIS: ' . $e->getMessage());
                    return self::FAILURE;
                }

                $totalPulled += $hasilTarik['pulled'];
                $totalSkippedPull += $hasilTarik['skipped'];
                $this->line("  Ditarik {$hasilTarik['pulled']}, dilewati {$hasilTarik['skipped']}");
            }

            if (!$hanyaTarik) {
                $hasilProses = $this->service->prosesKeAbsensi($tanggal);
                $totalProcessed += $hasilProses['processed'];
                $totalSkippedProcess += $hasilProses['skipped'];
                $this->line("  Diproses {$hasilProses['processed']}, dilewati {$hasilProses['skipped']}");
            }
        }

        $this->info('Selesai.');
        $this->info("Total ditarik  : {$totalPulled}");
        $this->line("Total dilewati tarik: {$totalSkippedPull}");
        if (!$hanyaTarik) {
            $this->info("Total diproses : {$totalProcessed} PJLP/hari");
            $this->line("Total dilewati proses: {$totalSkippedProcess}");
        }

        return self::SUCCESS;
    }
}
