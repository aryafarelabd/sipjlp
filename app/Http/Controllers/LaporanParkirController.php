<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanParkirRequest;
use App\Models\LaporanParkir;
use App\Models\LaporanParkirFoto;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanParkirController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $shifts = Shift::where('is_active', true)
            ->orderBy('jam_mulai')
            ->get();

        // Laporan hari ini milik petugas ini
        $laporanHariIni = LaporanParkir::with('fotos')
            ->where('pjlp_id', $pjlp->id)
            ->whereDate('tanggal', today())
            ->orderBy('created_at', 'desc')
            ->get();

        $laporanRoda4 = $laporanHariIni->where('jenis', 'roda_4');
        $laporanRoda2 = $laporanHariIni->where('jenis', 'roda_2');

        return view('laporan-parkir.index', compact(
            'pjlp', 'shifts', 'laporanRoda4', 'laporanRoda2'
        ));
    }

    public function store(StoreLaporanParkirRequest $request)
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return back()->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $laporan = LaporanParkir::create([
            'pjlp_id'          => $pjlp->id,
            'shift_id'         => $request->shift_id,
            'tanggal'          => today(),
            'jenis'            => $request->jenis,
            'jumlah_kendaraan' => $request->jumlah_kendaraan,
            'catatan'          => $request->catatan,
        ]);

        // Simpan foto
        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store(
                'laporan-parkir/' . now()->format('Y-m'),
                'public'
            );
            LaporanParkirFoto::create([
                'laporan_parkir_id' => $laporan->id,
                'path'              => $path,
            ]);
        }

        $jenis = $request->jenis === 'roda_4' ? 'Roda 4' : 'Roda 2';
        return back()->with('success', "Laporan parkir {$jenis} berhasil disimpan.");
    }

    public function rekap(Request $request)
    {
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');

        $query = LaporanParkir::with(['pjlp', 'shift'])
            ->byBulan($bulan, $tahun);

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        // Rekap per tanggal
        $rekapHarian = $query->get()
            ->groupBy(fn($r) => $r->tanggal->format('Y-m-d'))
            ->map(fn($group) => [
                'tanggal'  => Carbon::parse($group->first()->tanggal)->translatedFormat('d M Y'),
                'roda_4'   => $group->where('jenis', 'roda_4')->sum('jumlah_kendaraan'),
                'roda_2'   => $group->where('jenis', 'roda_2')->sum('jumlah_kendaraan'),
                'laporan'  => $group,
            ])
            ->values();

        $totalRoda4 = $rekapHarian->sum('roda_4');
        $totalRoda2 = $rekapHarian->sum('roda_2');

        return view('laporan-parkir.rekap', compact(
            'rekapHarian', 'bulan', 'tahun', 'search', 'totalRoda4', 'totalRoda2'
        ));
    }
}
