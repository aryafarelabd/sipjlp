<?php

namespace App\Http\Controllers;

use App\Models\GerakanJumatSehat;
use App\Models\Pjlp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GerakanJumatSehatController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        if ($user->isPjlp()) {
            $pjlp    = $user->pjlp;
            $riwayat = GerakanJumatSehat::where('pjlp_id', $pjlp->id)
                ->byBulan($bulan, $tahun)
                ->orderByDesc('tanggal')
                ->get();

            // Hitung kehadiran bulan ini
            $kehadiranBulanIni = $riwayat->count();

            return view('gerakan-jumat-sehat.index', compact(
                'riwayat', 'bulan', 'tahun', 'kehadiranBulanIni'
            ));
        }

        return $this->rekap($request);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return back()->with('error', 'Data PJLP tidak ditemukan.');
        }

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'waktu'   => 'nullable',
            'foto'    => 'required|image|max:10240',
        ]);

        $fotoPath = $request->file('foto')->store(
            'gerakan-jumat-sehat/' . now()->format('Y-m'),
            'public'
        );

        GerakanJumatSehat::create([
            'pjlp_id' => $pjlp->id,
            'unit'    => $pjlp->unit->value,
            'tanggal' => $request->tanggal,
            'waktu'   => $request->waktu ?: null,
            'foto'    => $fotoPath,
        ]);

        return back()->with('success', 'Kehadiran Gerakan Jumat Sehat berhasil dicatat.');
    }

    public function rekap(Request $request)
    {
        $user   = Auth::user();

        abort_unless($user->hasAnyRole(['admin', 'koordinator', 'chief']), 403);

        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');

        // Tentukan unit yang boleh dilihat
        $unitValue = $user->unit?->value ?? 'all';
        if ($user->hasRole('admin') || $unitValue === 'all') {
            $unitFilter = $request->get('unit_filter', '');
        } elseif ($unitValue === 'cleaning') {
            $unitFilter = 'cleaning';
        } else {
            $unitFilter = 'security';
        }

        // Rekap kehadiran per PJLP bulan ini
        $query = Pjlp::query()->whereNotNull('user_id');

        if ($unitFilter) {
            $query->where('unit', $unitFilter);
        }
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $pjlpList = $query->orderBy('nama')->get();

        // Ambil kehadiran bulan ini per pjlp
        $kehadiranQuery = GerakanJumatSehat::byBulan($bulan, $tahun);
        if ($unitFilter) {
            $kehadiranQuery->byUnit($unitFilter);
        }
        $kehadiranRaw = $kehadiranQuery->get()->groupBy('pjlp_id');

        // Hitung total entri
        $totalEntri = $kehadiranQuery->count();

        return view('gerakan-jumat-sehat.rekap', compact(
            'pjlpList', 'kehadiranRaw', 'bulan', 'tahun',
            'search', 'unitFilter', 'totalEntri'
        ));
    }
}
