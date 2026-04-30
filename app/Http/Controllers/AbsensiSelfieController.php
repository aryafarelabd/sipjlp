<?php

namespace App\Http\Controllers;

use App\Enums\SumberDataAbsensi;
use App\Exports\RekapAbsensiExport;
use App\Http\Requests\KoreksiAbsensiRequest;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Pjlp;
use App\Services\AbsensiSelfieService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiSelfieController extends Controller
{
    public function __construct(protected AbsensiSelfieService $service) {}

    /**
     * Halaman absensi bulan ini milik pegawai yang login.
     */
    public function showAbsenPage()
    {
        $pjlp = auth()->user()->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil PJLP tidak ditemukan. Hubungi administrator.');
        }

        $bulan       = now()->month;
        $tahun       = now()->year;
        $bulanCarbon = Carbon::create($tahun, $bulan, 1);
        $startOfMonth = $bulanCarbon->copy()->startOfMonth();
        $endOfMonth   = $bulanCarbon->copy()->endOfMonth();

        $absensiMap = Absensi::where('pjlp_id', $pjlp->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with('shift')
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->tanggal)->format('Y-m-d'));

        $jadwalMap = $this->service->getJadwalHarianMap($pjlp, $startOfMonth, $endOfMonth);

        $hariList = [];
        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            $key     = $d->format('Y-m-d');
            $jadwal  = $jadwalMap[$key] ?? null;

            $hariList[] = [
                'tanggal' => $d->copy(),
                'shift'   => $jadwal['shift'] ?? null,
                'is_kerja'=> $jadwal['is_kerja'] ?? false,
                'absensi' => $absensiMap[$key] ?? null,
            ];
        }

        return view('absensi.selfie.absen', compact('pjlp', 'hariList', 'bulan', 'tahun'));
    }

    /**
     * Rekap absensi — summary per PJLP atau detail per hari jika pjlp_id diberikan.
     */
    public function rekapAbsensi(Request $request)
    {
        $user = auth()->user();

        abort_unless($this->canViewRekapAbsensi($user), 403);

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun       = (int) $request->input('tahun', now()->year);
        $bulanCarbon = Carbon::create($tahun, $bulan, 1);

        // Trigger alpha detection (Opsi C)
        if ($user->can('absensi.view-all')) {
            $this->service->markAlphaAll($bulanCarbon);
        } elseif ($user->can('absensi.view-unit') && $user->unit) {
            $this->service->markAlphaForUnit($user->unit, $bulanCarbon);
        }

        // Jika ada pjlp_id → tampilkan detail per hari untuk 1 PJLP
        if ($request->filled('pjlp_id')) {
            return $this->rekapDetail($request, $bulan, $tahun, $bulanCarbon);
        }

        // --- Summary: daftar PJLP dengan rekapan sebulan ---
        $pjlpQuery = Pjlp::active()->with('user')->orderBy('nama');

        if (!$user->can('absensi.view-all') && $user->unit) {
            $pjlpQuery->unit($user->unit);
        }

        if ($request->filled('search')) {
            $pjlpQuery->where('nama', 'like', '%' . $request->search . '%');
        }

        $pjlpList    = $pjlpQuery->get();
        $startOfMonth = $bulanCarbon->copy()->startOfMonth();
        $endOfMonth   = $bulanCarbon->copy()->endOfMonth();

        // Ambil semua absensi bulan ini sekaligus (eager)
        $pjlpIds     = $pjlpList->pluck('id');
        $absensiMap  = Absensi::whereIn('pjlp_id', $pjlpIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with('shift')
            ->get()
            ->groupBy('pjlp_id');

        // Hitung jadwal hari kerja per PJLP sebulan (dari jadwal/jadwal_shift_cs)
        $jadwalMap = $this->service->getJadwalBulananMap($pjlpList, $startOfMonth, $endOfMonth);

        // Susun summary per PJLP
        $summaryList = $pjlpList->map(function (Pjlp $pjlp) use ($absensiMap, $jadwalMap) {
            $absensiPjlp  = $absensiMap->get($pjlp->id, collect());
            $jadwalPjlp   = $jadwalMap[$pjlp->id] ?? collect();

            $hariKerja    = $jadwalPjlp->count();
            $totalAlpha   = $absensiPjlp->where('status.value', 'alpha')->count();
            $totalIzin    = $absensiPjlp->whereIn('status.value', ['izin', 'cuti'])->count();

            // Total telat (menit)
            $totalTelatMenit = $absensiPjlp
                ->where('status.value', 'terlambat')
                ->sum('menit_terlambat');

            // Total pulang cepat (menit) — hanya dari yang ada absensi + shift
            $totalPulangCepatMenit = 0;
            foreach ($absensiPjlp as $abs) {
                if (!$abs->shift) continue;
                $tgl          = Carbon::parse($abs->tanggal);
                $shiftSelesai = Carbon::parse($tgl->format('Y-m-d') . ' ' . Carbon::parse($abs->shift->jam_selesai)->format('H:i:s'));
                if ($shiftSelesai->lte(Carbon::parse($tgl->format('Y-m-d') . ' ' . Carbon::parse($abs->shift->jam_mulai)->format('H:i:s')))) {
                    $shiftSelesai->addDay();
                }
                if ($abs->jam_masuk && !$abs->jam_pulang) {
                    $totalPulangCepatMenit += 225;
                } elseif ($abs->jam_masuk && $abs->jam_pulang) {
                    $jamPulang = Carbon::parse($abs->jam_pulang);
                    $selisih   = (int) $jamPulang->diffInMinutes($shiftSelesai, false);
                    if ($selisih > 0) {
                        $totalPulangCepatMenit += $selisih;
                    }
                }
            }

            return [
                'pjlp'               => $pjlp,
                'hari_kerja'         => $hariKerja,
                'total_alpha'        => $totalAlpha,
                'total_izin'         => $totalIzin,
                'total_telat_menit'  => $totalTelatMenit,
                'total_pulang_cepat' => $totalPulangCepatMenit,
            ];
        });

        return view('absensi.selfie.rekap', compact('summaryList', 'bulan', 'tahun'));
    }

    /**
     * Koreksi absensi manual oleh koordinator/admin.
     */
    public function simpanKoreksi(KoreksiAbsensiRequest $request)
    {
        $user    = auth()->user();

        abort_unless($user->isAdmin(), 403);

        $pjlp    = Pjlp::findOrFail($request->pjlp_id);
        $tanggal = Carbon::parse($request->tanggal);

        if (!$user->can('absensi.view-all') && $user->unit) {
            abort_if(
                $pjlp->unit !== $user->unit,
                403,
                'Anda tidak memiliki akses ke data PJLP unit lain.'
            );
        }

        $absensi = Absensi::firstOrNew([
            'pjlp_id' => $pjlp->id,
            'tanggal' => $tanggal->toDateString(),
        ]);

        $before = $absensi->exists ? $absensi->toArray() : null;

        $absensi->status      = $request->status;
        $absensi->sumber_data = SumberDataAbsensi::MANUAL;
        $absensi->keterangan  = $request->keterangan;

        if ($request->filled('jam_masuk')) {
            $absensi->jam_masuk = $tanggal->format('Y-m-d') . ' ' . $request->jam_masuk . ':00';
        }
        if ($request->filled('jam_pulang')) {
            $absensi->jam_pulang = $tanggal->format('Y-m-d') . ' ' . $request->jam_pulang . ':00';
        }

        $absensi->save();

        AuditLog::log('Koreksi absensi manual', $absensi, $before, $absensi->toArray());

        $bulan = $tanggal->month;
        $tahun = $tanggal->year;

        return redirect()
            ->route('absensi.rekap', ['pjlp_id' => $pjlp->id, 'bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Koreksi absensi ' . $pjlp->nama . ' tanggal ' . $tanggal->format('d/m/Y') . ' berhasil disimpan.');
    }

    /**
     * Export rekap absensi ke Excel.
     */
    public function exportRekap(Request $request)
    {
        abort_unless($this->canViewRekapAbsensi(auth()->user()), 403);

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $user  = auth()->user();

        $unitFilter = null;
        if (!$user->can('absensi.view-all') && $user->unit) {
            $unitFilter = $user->unit->value;
        }

        $filename = 'rekap-absensi-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.xlsx';

        return Excel::download(new RekapAbsensiExport($bulan, $tahun, $unitFilter), $filename);
    }

    /**
     * Detail absensi per hari untuk satu PJLP.
     */
    private function rekapDetail(Request $request, int $bulan, int $tahun, Carbon $bulanCarbon): \Illuminate\View\View
    {
        $user = auth()->user();
        $pjlp = Pjlp::findOrFail($request->pjlp_id);

        // Koordinator hanya boleh lihat PJLP di unitnya sendiri
        if (!$user->can('absensi.view-all') && $user->unit) {
            abort_if(
                $pjlp->unit !== $user->unit,
                403,
                'Anda tidak memiliki akses ke data PJLP unit lain.'
            );
        }

        $startOfMonth = $bulanCarbon->copy()->startOfMonth();
        $endOfMonth   = $bulanCarbon->copy()->endOfMonth();

        // Semua absensi PJLP ini bulan ini
        $absensiMap = Absensi::where('pjlp_id', $pjlp->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with('shift')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->tanggal)->format('Y-m-d'));

        // Semua jadwal PJLP ini bulan ini
        $jadwalMap = $this->service->getJadwalHarianMap($pjlp, $startOfMonth, $endOfMonth);

        // Generate semua hari dalam sebulan
        $hariList = [];
        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            $key      = $d->format('Y-m-d');
            $absensi  = $absensiMap[$key] ?? null;
            $jadwal   = $jadwalMap[$key] ?? null;

            $hariList[] = [
                'tanggal' => $d->copy(),
                'shift'   => $jadwal['shift'] ?? null,
                'is_kerja'=> $jadwal['is_kerja'] ?? false,
                'absensi' => $absensi,
            ];
        }

        return view('absensi.selfie.rekap_detail', compact('pjlp', 'hariList', 'bulan', 'tahun'));
    }

    private function canViewRekapAbsensi($user): bool
    {
        if ($user->hasAnyRole(['pjlp', 'danru'])) {
            return false;
        }

        return $user->canAny(['absensi.view-unit', 'absensi.view-all']);
    }
}
