<?php

namespace App\Services;

use App\Enums\StatusAbsensi;
use App\Enums\SumberDataAbsensi;
use App\Enums\UnitType;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\JadwalShiftCs;
use App\Models\Pjlp;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\AbsensiAlertNotification;
use Carbon\Carbon;

class AbsensiSelfieService
{
    /**
     * Ambil jadwal shift PJLP hari ini.
     * Security → tabel jadwal (is_published)
     * CS → tabel jadwal_shift_cs (status = normal)
     *
     * @return array{jadwal: Jadwal|JadwalShiftCs|null, shift: Shift|null, is_kerja: bool}
     */
    public function getJadwalForPjlp(Pjlp $pjlp, Carbon $tanggal): array
    {
        $result = ['jadwal' => null, 'shift' => null, 'is_kerja' => false];

        if ($pjlp->unit === UnitType::SECURITY) {
            $jadwal = Jadwal::with('shift')
                ->where('pjlp_id', $pjlp->id)
                ->whereDate('tanggal', $tanggal)
                ->where('is_published', true)
                ->first();

            if ($jadwal && $jadwal->shift) {
                $result['jadwal']   = $jadwal;
                $result['shift']    = $jadwal->shift;
                $result['is_kerja'] = true;
            }
        } elseif ($pjlp->unit === UnitType::CLEANING) {
            $jadwal = JadwalShiftCs::with('shift')
                ->where('pjlp_id', $pjlp->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            if ($jadwal && $jadwal->shift) {
                $result['jadwal']   = $jadwal;
                $result['shift']    = $jadwal->shift;
                $result['is_kerja'] = $jadwal->isKerja();
            }
        }

        return $result;
    }

    /**
     * Mark alpha untuk satu PJLP selama satu bulan (Opsi C - lazy detection).
     * Dipanggil saat koordinator/admin membuka halaman rekap.
     */
    public function markAlphaForPjlp(Pjlp $pjlp, Carbon $bulan): int
    {
        $startDate = $bulan->copy()->startOfMonth();
        $endDate   = Carbon::yesterday()->lt($bulan->copy()->endOfMonth())
            ? Carbon::yesterday()
            : $bulan->copy()->endOfMonth();

        // Jika bulan yang dipilih adalah bulan depan, tidak ada yang perlu di-mark
        if ($startDate->isFuture()) {
            return 0;
        }

        $count = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $jadwalInfo = $this->getJadwalForPjlp($pjlp, $date);

            // Skip jika bukan hari kerja
            if (!$jadwalInfo['is_kerja'] || !$jadwalInfo['shift']) {
                continue;
            }

            $shift = $jadwalInfo['shift'];

            // Cek apakah record absensi sudah ada
            $absensi = Absensi::where('pjlp_id', $pjlp->id)
                ->whereDate('tanggal', $date)
                ->first();

            // Skip jika sudah alpha (idempotent)
            if ($absensi && $absensi->status === StatusAbsensi::ALPHA) {
                continue;
            }

            // Skip jika sudah absen masuk
            if ($absensi && $absensi->jam_masuk) {
                continue;
            }

            // Buat atau update record sebagai alpha (idempotent via updateOrCreate)
            Absensi::updateOrCreate(
                ['pjlp_id' => $pjlp->id, 'tanggal' => $date->toDateString()],
                [
                    'shift_id'        => $shift->id,
                    'status'          => StatusAbsensi::ALPHA,
                    'menit_terlambat' => 0,
                    'sumber_data'     => SumberDataAbsensi::MANUAL,
                    'keterangan'      => 'Alpha otomatis - tidak absen masuk',
                ]
            );

            // Notifikasi koordinator unit untuk alpha baru
            $koordinators = User::role('koordinator')
                ->where('unit', $pjlp->unit)
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($koordinators as $koordinator) {
                $koordinator->notify(new AbsensiAlertNotification($pjlp, 'alpha'));
            }

            $count++;
        }

        return $count;
    }

    /**
     * Ambil map jadwal untuk banyak PJLP dalam rentang tanggal.
     * Return: [ pjlp_id => Collection of jadwal info yang is_kerja ]
     */
    public function getJadwalBulananMap($pjlpList, Carbon $start, Carbon $end): array
    {
        $map = [];
        foreach ($pjlpList as $pjlp) {
            $map[$pjlp->id] = collect();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $info = $this->getJadwalForPjlp($pjlp, $d->copy());
                if ($info['is_kerja'] && $info['shift']) {
                    $map[$pjlp->id]->push($info);
                }
            }
        }
        return $map;
    }

    /**
     * Ambil map jadwal harian untuk satu PJLP dalam rentang tanggal.
     * Return: [ 'Y-m-d' => ['shift' => Shift|null, 'is_kerja' => bool] ]
     */
    public function getJadwalHarianMap(Pjlp $pjlp, Carbon $start, Carbon $end): array
    {
        $map = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $info = $this->getJadwalForPjlp($pjlp, $d->copy());
            $map[$d->format('Y-m-d')] = [
                'shift'    => $info['shift'],
                'is_kerja' => $info['is_kerja'],
            ];
        }
        return $map;
    }

    /**
     * Mark alpha untuk semua PJLP aktif (dipanggil oleh admin).
     */
    public function markAlphaAll(Carbon $bulan): int
    {
        $total = 0;
        $pjlps = Pjlp::active()->get();

        foreach ($pjlps as $pjlp) {
            $total += $this->markAlphaForPjlp($pjlp, $bulan);
        }

        return $total;
    }

    /**
     * Mark alpha untuk PJLP dalam satu unit (dipanggil oleh koordinator).
     */
    public function markAlphaForUnit(UnitType $unit, Carbon $bulan): int
    {
        $total = 0;
        $pjlps = Pjlp::active()->unit($unit)->get();

        foreach ($pjlps as $pjlp) {
            $total += $this->markAlphaForPjlp($pjlp, $bulan);
        }

        return $total;
    }
}
