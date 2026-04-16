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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
     * Hitung window waktu absen masuk.
     * Buka: shift_start - 1 jam
     * Tutup: shift_start + 1 jam
     */
    public function getWindowMasuk(Shift $shift, Carbon $tanggal): array
    {
        $base = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_mulai->format('H:i:s'));

        return [
            'open'  => $base->copy()->subHour(),
            'close' => $base->copy()->addHour(),
        ];
    }

    /**
     * Hitung window waktu absen pulang.
     * Buka: shift_end
     * Tutup: shift_end + 2 jam
     */
    public function getWindowPulang(Shift $shift, Carbon $tanggal): array
    {
        $base = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_selesai->format('H:i:s'));

        return [
            'open'  => $base->copy(),
            'close' => $base->copy()->addHours(2),
        ];
    }

    /**
     * Cek apakah PJLP boleh absen masuk sekarang.
     */
    public function checkInAllowed(?Absensi $absensi, Shift $shift, Carbon $now, Carbon $tanggal): array
    {
        if ($absensi && $absensi->jam_masuk) {
            return ['allowed' => false, 'reason' => 'Anda sudah absen masuk hari ini.'];
        }

        $window = $this->getWindowMasuk($shift, $tanggal);

        if ($now->lt($window['open'])) {
            return [
                'allowed' => false,
                'reason'  => 'Belum waktunya absen. Absen masuk dibuka pukul ' . $window['open']->format('H:i') . '.',
                'window'  => $window,
            ];
        }

        if ($now->gt($window['close'])) {
            return [
                'allowed' => false,
                'reason'  => 'Waktu absen masuk sudah ditutup sejak pukul ' . $window['close']->format('H:i') . '. Absensi hari ini dicatat sebagai ALPHA.',
                'window'  => $window,
            ];
        }

        return ['allowed' => true, 'reason' => null, 'window' => $window];
    }

    /**
     * Cek apakah PJLP boleh absen pulang sekarang.
     */
    public function checkOutAllowed(?Absensi $absensi, Shift $shift, Carbon $now, Carbon $tanggal): array
    {
        if (!$absensi || !$absensi->jam_masuk) {
            return ['allowed' => false, 'reason' => 'Anda belum absen masuk hari ini.'];
        }

        if ($absensi->jam_pulang) {
            return ['allowed' => false, 'reason' => 'Anda sudah absen pulang hari ini.'];
        }

        $window = $this->getWindowPulang($shift, $tanggal);

        if ($now->lt($window['open'])) {
            return [
                'allowed' => false,
                'reason'  => 'Absen pulang dibuka pukul ' . $window['open']->format('H:i') . '.',
                'window'  => $window,
            ];
        }

        if ($now->gt($window['close'])) {
            return [
                'allowed' => false,
                'reason'  => 'Waktu absen pulang sudah ditutup sejak pukul ' . $window['close']->format('H:i') . '.',
                'window'  => $window,
            ];
        }

        return ['allowed' => true, 'reason' => null, 'window' => $window];
    }

    /**
     * Simpan foto selfie ke storage.
     * Path: absensi-selfie/{YYYY-MM}/{pjlp_id}_{type}_{timestamp}.jpg
     */
    public function storeSelfiePhoto(UploadedFile $file, string $type, Pjlp $pjlp): string
    {
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];

        abort_unless(
            in_array($file->getMimeType(), $allowedMimes),
            422,
            'File foto harus berupa gambar JPEG atau PNG.'
        );

        $folder   = 'absensi-selfie/' . now()->format('Y-m');
        $ext      = in_array($file->extension(), ['jpg', 'jpeg', 'png']) ? $file->extension() : 'jpg';
        $filename = $pjlp->id . '_' . $type . '_' . now()->timestamp . '.' . $ext;

        return $file->storeAs($folder, $filename, 'public');
    }

    /**
     * Proses absen masuk — buat atau update record absensi.
     */
    public function processAbsenMasuk(
        Pjlp $pjlp,
        Shift $shift,
        Carbon $now,
        string $fotoPath,
        ?float $lat,
        ?float $lon
    ): Absensi {
        return DB::transaction(function () use ($pjlp, $shift, $now, $fotoPath, $lat, $lon) {
            $jamShift  = Carbon::parse($now->toDateString() . ' ' . $shift->jam_mulai->format('H:i:s'));
            $batas     = $jamShift->copy()->addMinutes($shift->toleransi_terlambat ?? 0);
            $menit     = $now->gt($batas) ? (int) abs($now->diffInMinutes($jamShift)) : 0;
            $status    = $menit > 0 ? StatusAbsensi::TERLAMBAT : StatusAbsensi::HADIR;

            return Absensi::updateOrCreate(
                ['pjlp_id' => $pjlp->id, 'tanggal' => $now->toDateString()],
                [
                    'shift_id'        => $shift->id,
                    'jam_masuk'       => $now->format('H:i:s'),
                    'status'          => $status,
                    'menit_terlambat' => $menit,
                    'sumber_data'     => SumberDataAbsensi::SELFIE,
                    'foto_masuk'      => $fotoPath,
                    'latitude_masuk'  => $lat,
                    'longitude_masuk' => $lon,
                ]
            );
        });
    }

    /**
     * Proses absen pulang — update record absensi yang sudah ada.
     */
    public function processAbsenPulang(
        Absensi $absensi,
        Carbon $now,
        string $fotoPath,
        ?float $lat,
        ?float $lon
    ): Absensi {
        return DB::transaction(function () use ($absensi, $now, $fotoPath, $lat, $lon) {
            $absensi->update([
                'jam_pulang'       => $now->format('H:i:s'),
                'foto_pulang'      => $fotoPath,
                'latitude_pulang'  => $lat,
                'longitude_pulang' => $lon,
            ]);

            return $absensi->fresh();
        });
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
