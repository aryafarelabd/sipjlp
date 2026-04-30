<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\LembarKerja;
use App\Models\LogAbsensiMesin;
use App\Models\Pjlp;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function absensi(Request $request)
    {
        $user = $request->user();

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // Baca dari log_absensi_mesin
        $query = LogAbsensiMesin::with('pjlp')
            ->whereMonth('check_time', $bulan)
            ->whereYear('check_time', $tahun)
            ->whereNotNull('pjlp_id');

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        if ($request->filled('unit')) {
            $query->whereHas('pjlp', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        $logAbsensi = $query->orderBy('check_time')->get();

        // Group by PJLP dan hitung jumlah scan per hari
        $rekapPerPjlp = $logAbsensi->groupBy('pjlp_id')->map(function ($items) {
            $pjlp = $items->first()->pjlp;

            // Hitung jumlah hari unik dengan scan masuk
            $hariMasuk = $items->where('check_type', 'I')
                ->groupBy(fn($item) => $item->check_time->format('Y-m-d'))
                ->count();

            // Hitung jumlah hari unik dengan scan pulang
            $hariPulang = $items->where('check_type', 'O')
                ->groupBy(fn($item) => $item->check_time->format('Y-m-d'))
                ->count();

            // Total scan
            $totalMasuk = $items->where('check_type', 'I')->count();
            $totalPulang = $items->where('check_type', 'O')->count();

            return [
                'pjlp' => $pjlp,
                'hari_masuk' => $hariMasuk,
                'hari_pulang' => $hariPulang,
                'total_masuk' => $totalMasuk,
                'total_pulang' => $totalPulang,
                'total_scan' => $items->count(),
            ];
        });

        return view('laporan.absensi', compact('rekapPerPjlp', 'bulan', 'tahun'));
    }

    public function exportAbsensi(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // Generate PDF
        $data = $this->getAbsensiData($request);

        $pdf = Pdf::loadView('laporan.export.absensi-pdf', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode' => Carbon::create($tahun, $bulan)->format('F Y'),
        ]);

        return $pdf->download("laporan-absensi-{$tahun}-{$bulan}.pdf");
    }

    private function getAbsensiData(Request $request)
    {
        $user = $request->user();
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = Absensi::with(['pjlp', 'shift'])->forMonth($tahun, $bulan);

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        return $query->orderBy('tanggal')->get()->groupBy('pjlp_id');
    }

    public function cuti(Request $request)
    {
        $user = $request->user();

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = Cuti::with(['pjlp', 'jenisCuti', 'approvedBy'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        if ($request->filled('unit')) {
            $query->whereHas('pjlp', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cuti = $query->orderBy('created_at', 'desc')->get();

        // Summary
        $summary = [
            'total' => $cuti->count(),
            'menunggu' => $cuti->where('status', 'menunggu')->count(),
            'disetujui' => $cuti->where('status', 'disetujui')->count(),
            'ditolak' => $cuti->where('status', 'ditolak')->count(),
            'total_hari' => $cuti->where('status', 'disetujui')->sum('jumlah_hari'),
        ];

        return view('laporan.cuti', compact('cuti', 'summary', 'bulan', 'tahun'));
    }

    public function exportCuti(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $data = $this->getCutiData($request);

        $pdf = Pdf::loadView('laporan.export.cuti-pdf', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode' => Carbon::create($tahun, $bulan)->format('F Y'),
        ]);

        return $pdf->download("laporan-cuti-{$tahun}-{$bulan}.pdf");
    }

    private function getCutiData(Request $request)
    {
        $user = $request->user();
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = Cuti::with(['pjlp', 'jenisCuti'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function lembarKerja(Request $request)
    {
        $user = $request->user();

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = LembarKerja::with(['pjlp', 'details', 'validasi'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        if ($request->filled('unit')) {
            $query->whereHas('pjlp', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lembarKerja = $query->orderBy('tanggal', 'desc')->get();

        // Summary
        $summary = [
            'total' => $lembarKerja->count(),
            'draft' => $lembarKerja->where('status', 'draft')->count(),
            'submitted' => $lembarKerja->where('status', 'submitted')->count(),
            'divalidasi' => $lembarKerja->where('status', 'divalidasi')->count(),
            'ditolak' => $lembarKerja->where('status', 'ditolak')->count(),
        ];

        return view('laporan.lembar-kerja', compact('lembarKerja', 'summary', 'bulan', 'tahun'));
    }

    public function exportLembarKerja(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $data = $this->getLembarKerjaData($request);

        $pdf = Pdf::loadView('laporan.export.lembar-kerja-pdf', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode' => Carbon::create($tahun, $bulan)->format('F Y'),
        ]);

        return $pdf->download("laporan-lembar-kerja-{$tahun}-{$bulan}.pdf");
    }

    private function getLembarKerjaData(Request $request)
    {
        $user = $request->user();
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = LembarKerja::with(['pjlp', 'details'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }
}
