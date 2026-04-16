<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestrictToShiftHours
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Admin, koordinator, danru, chief selalu boleh akses (mereka lihat rekap)
        if ($user->hasAnyRole(['admin', 'koordinator', 'danru', 'chief', 'manajemen'])) {
            return $next($request);
        }

        $pjlp = DB::table('pjlp')->where('user_id', $user->id)->first();
        if (!$pjlp) {
            return $this->errorResponse($request, 'Profil PJLP tidak ditemukan.');
        }

        $now = Carbon::now();

        // Jika jam 00:00–08:00 bisa jadi masih shift malam dari kemarin
        $checkDates = [$now->toDateString()];
        if ($now->hour < 8) {
            $checkDates[] = $now->copy()->subDay()->toDateString();
        }

        // Cek jadwal berdasarkan unit PJLP
        $jadwalShift = $this->getJadwalShift($pjlp, $checkDates);

        if ($jadwalShift->isEmpty()) {
            return $this->errorResponse($request, 'Anda tidak memiliki jadwal shift aktif saat ini.');
        }

        foreach ($jadwalShift as $jadwal) {
            $start = Carbon::parse($jadwal->tanggal . ' ' . $jadwal->jam_mulai)->subMinutes(30);
            $end   = Carbon::parse($jadwal->tanggal . ' ' . $jadwal->jam_selesai)->addMinutes(30);

            // Shift malam — melewati tengah malam
            if (Carbon::parse($jadwal->jam_selesai)->lt(Carbon::parse($jadwal->jam_mulai))) {
                $end->addDay();
            }

            if ($now->between($start, $end)) {
                return $next($request);
            }
        }

        $info = $jadwalShift->first();
        return $this->errorResponse(
            $request,
            'Saat ini bukan jam shift Anda. Akses ditutup. ' .
            '(Jadwal: ' . substr($info->jam_mulai, 0, 5) . '–' . substr($info->jam_selesai, 0, 5) . ')'
        );
    }

    /**
     * Ambil jadwal shift PJLP hari ini dari tabel yang sesuai (CS atau Security).
     */
    private function getJadwalShift(object $pjlp, array $checkDates): \Illuminate\Support\Collection
    {
        // Cleaning Service → jadwal_shift_cs (hanya yang status = normal)
        if ($pjlp->unit === 'cleaning') {
            return DB::table('jadwal_shift_cs')
                ->join('shifts', 'jadwal_shift_cs.shift_id', '=', 'shifts.id')
                ->where('jadwal_shift_cs.pjlp_id', $pjlp->id)
                ->where('jadwal_shift_cs.status', 'normal')
                ->whereNotNull('jadwal_shift_cs.shift_id')
                ->whereIn(DB::raw('DATE(jadwal_shift_cs.tanggal)'), $checkDates)
                ->select(
                    DB::raw('DATE(jadwal_shift_cs.tanggal) as tanggal'),
                    'shifts.jam_mulai',
                    'shifts.jam_selesai'
                )
                ->get();
        }

        // Security → tabel jadwal (hanya yang is_published = true)
        if ($pjlp->unit === 'security') {
            return DB::table('jadwal')
                ->join('shifts', 'jadwal.shift_id', '=', 'shifts.id')
                ->where('jadwal.pjlp_id', $pjlp->id)
                ->where('jadwal.is_published', true)
                ->whereNotNull('jadwal.shift_id')
                ->whereIn(DB::raw('DATE(jadwal.tanggal)'), $checkDates)
                ->select(
                    DB::raw('DATE(jadwal.tanggal) as tanggal'),
                    'shifts.jam_mulai',
                    'shifts.jam_selesai'
                )
                ->get();
        }

        return collect();
    }

    private function errorResponse($request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }
        return redirect()->route('dashboard')->with('error', $message);
    }
}
