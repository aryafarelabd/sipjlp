<?php

namespace App\Services;

use App\Enums\StatusAbsensi;
use App\Enums\SumberDataAbsensi;
use App\Models\Absensi;
use App\Models\LogAbsensiMesin;
use App\Models\Pjlp;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarikAbsenMesinService
{
    /**
     * Tarik data dari tabel log NPIS ke log_absensi_mesin SIPJLP.
     *
     * @return array{pulled: int, skipped: int}
     */
    public function tarikDariNpis(?string $tanggal = null): array
    {
        $tahun  = $tanggal ? Carbon::parse($tanggal)->year : now()->year;
        $filter = $tanggal ? '%' . $tanggal . '%' : '%' . $tahun . '%';

        $rows = DB::connection('npis')
            ->table('log')
            ->where('tanggal', 'like', $filter)
            ->orderBy('tanggal')
            ->get(['user', 'tanggal', 'status']);

        $badgeMap = Pjlp::whereNotNull('badge_number')
            ->pluck('id', 'badge_number')
            ->toArray();

        $pulled  = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $checkType = strtoupper($row->status);
            if (!in_array($checkType, ['I', 'O'])) {
                $skipped++;
                continue;
            }

            $checkTime = Carbon::parse($row->tanggal);
            $badge     = (string) $row->user;
            $pjlpId    = $badgeMap[$badge] ?? null;

            $exists = LogAbsensiMesin::where('badge_number', $badge)
                ->where('check_time', $checkTime)
                ->where('check_type', $checkType)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            LogAbsensiMesin::create([
                'badge_number' => $badge,
                'check_time'   => $checkTime,
                'check_type'   => $checkType,
                'pjlp_id'      => $pjlpId,
                'is_processed' => false,
            ]);

            $pulled++;
        }

        return ['pulled' => $pulled, 'skipped' => $skipped];
    }

    /**
     * Proses log_absensi_mesin ke tabel absensi dengan 3-level fallback shift.
     *
     * Level 1: Ada jadwal di SIPJLP → pakai shift dari jadwal
     * Level 2: Tidak ada jadwal → auto-detect shift dari bi/ai
     * Level 3: Shift tidak terdeteksi → simpan jam masuk/pulang, status HADIR
     *
     * @return array{processed: int, skipped: int}
     */
    public function prosesKeAbsensi(?string $tanggal = null): array
    {
        $query = LogAbsensiMesin::unprocessed()->whereNotNull('pjlp_id');

        if ($tanggal) {
            $query->whereDate('check_time', $tanggal);
        }

        $logs = $query->get()->groupBy(function ($log) {
            return $log->pjlp_id . '_' . $log->check_time->toDateString();
        });

        $processed = 0;
        $skipped   = 0;

        foreach ($logs as $key => $group) {
            [$pjlpId, $tanggalStr] = explode('_', $key, 2);

            $pjlp = Pjlp::find($pjlpId);
            if (!$pjlp) {
                $skipped++;
                continue;
            }

            $logIn  = $group->where('check_type', 'I')->sortBy('check_time')->first();
            $logOut = $group->where('check_type', 'O')->sortByDesc('check_time')->first();

            if (!$logIn) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($pjlp, $tanggalStr, $logIn, $logOut, $group) {
                    $jamMasuk = $logIn->check_time;

                    // Level 1: Cek jadwal di SIPJLP
                    $shift = $this->getShiftDariJadwal($pjlp, $tanggalStr);

                    // Level 2: Auto-detect dari bi/ai jika tidak ada jadwal
                    if (!$shift) {
                        $shift = $this->detectShiftDariJamTap($jamMasuk);
                    }

                    // Level 3: Tidak ada shift → HADIR tanpa kalkulasi
                    $menit  = 0;
                    $status = StatusAbsensi::HADIR;

                    if ($shift) {
                        $jamShift = Carbon::parse($tanggalStr . ' ' . $shift->jam_mulai->format('H:i:s'));
                        $batas    = $jamShift->copy()->addMinutes($shift->toleransi_terlambat ?? 0);

                        if ($jamMasuk->gt($batas)) {
                            $menit  = (int) abs($jamMasuk->diffInMinutes($jamShift));
                            $status = StatusAbsensi::TERLAMBAT;
                        }
                    }

                    Absensi::updateOrCreate(
                        ['pjlp_id' => $pjlp->id, 'tanggal' => $tanggalStr],
                        [
                            'shift_id'        => $shift?->id,
                            'jam_masuk'       => $jamMasuk->format('H:i:s'),
                            'jam_pulang'      => $logOut?->check_time->format('H:i:s'),
                            'status'          => $status,
                            'menit_terlambat' => $menit,
                            'sumber_data'     => SumberDataAbsensi::MESIN,
                        ]
                    );

                    $group->each(fn($log) => $log->update(['is_processed' => true]));
                });

                $processed++;
            } catch (\Exception $e) {
                Log::error('TarikAbsen: gagal pjlp_id=' . $pjlpId . ' tanggal=' . $tanggalStr . ' — ' . $e->getMessage());
                $skipped++;
            }
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Level 1: Ambil shift dari jadwal SIPJLP yang sudah diinput.
     */
    private function getShiftDariJadwal(Pjlp $pjlp, string $tanggal): ?Shift
    {
        $jadwalInfo = app(AbsensiSelfieService::class)
            ->getJadwalForPjlp($pjlp, Carbon::parse($tanggal));

        return $jadwalInfo['shift'] ?? null;
    }

    /**
     * Level 2: Auto-detect shift dari jam tap menggunakan window bi/ai.
     * Cocokkan: bi < jam_masuk < ai
     */
    private function detectShiftDariJamTap(Carbon $jamMasuk): ?Shift
    {
        $jamStr = $jamMasuk->format('H:i:s');

        return Shift::active()
            ->whereNotNull('bi')
            ->whereNotNull('ai')
            ->where('bi', '<', $jamStr)
            ->where('ai', '>', $jamStr)
            ->first();
    }
}
