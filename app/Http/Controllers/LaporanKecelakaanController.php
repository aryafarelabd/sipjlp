<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LaporanKecelakaanController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $riwayat = LaporanKecelakaan::where('user_id', $user->id)
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal')
            ->get();

        return view('laporan-kecelakaan.index', compact('riwayat', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama_pelapor'      => 'required|string|max:255',
            'unit_bagian'       => 'required|string|max:255',
            'tanggal'           => 'required|date|before_or_equal:today',
            'waktu'             => 'required',
            'tempat'            => 'required|string|max:255',
            'saksi'             => 'required|string',
            'jumlah_laki'       => 'required|integer|min:0|max:10',
            'jumlah_perempuan'  => 'required|integer|min:0|max:10',
            'nama_korban'       => 'required|string',
            'umur_korban'       => 'required|string|max:100',
            'akibat_mati'       => 'required|integer|min:0|max:10',
            'akibat_luka_berat' => 'required|integer|min:0|max:10',
            'akibat_luka_ringan'=> 'required|integer|min:0|max:10',
            'keterangan_cedera' => 'required|string',
            'kondisi_berbahaya' => 'required|string',
            'tindakan_berbahaya'=> 'required|string',
            'uraian_kejadian'   => 'required|string',
            'sumber_kejadian'   => 'required|string',
            'tipe'              => 'required|in:accident,incident,near_miss',
            'foto_bukti'        => 'required|file|mimes:jpeg,jpg,png|max:10240',
            'file_formulir'     => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $fotoBuktiPath    = null;
        $fileFormulirPath = null;

        try {
            $fotoBuktiPath    = $request->file('foto_bukti')->store('laporan-kecelakaan/foto/' . now()->format('Y-m'), 'public');
            $fileFormulirPath = $request->file('file_formulir')->store('laporan-kecelakaan/formulir/' . now()->format('Y-m'), 'public');

            LaporanKecelakaan::create([
                'user_id'           => $user->id,
                'nama_pelapor'      => $validated['nama_pelapor'],
                'unit_bagian'       => $validated['unit_bagian'],
                'tanggal'           => $validated['tanggal'],
                'waktu'             => $validated['waktu'],
                'tempat'            => $validated['tempat'],
                'saksi'             => $validated['saksi'],
                'jumlah_laki'       => $validated['jumlah_laki'],
                'jumlah_perempuan'  => $validated['jumlah_perempuan'],
                'nama_korban'       => $validated['nama_korban'],
                'umur_korban'       => $validated['umur_korban'],
                'akibat_mati'       => $validated['akibat_mati'],
                'akibat_luka_berat' => $validated['akibat_luka_berat'],
                'akibat_luka_ringan'=> $validated['akibat_luka_ringan'],
                'keterangan_cedera' => $validated['keterangan_cedera'],
                'kondisi_berbahaya' => $validated['kondisi_berbahaya'],
                'tindakan_berbahaya'=> $validated['tindakan_berbahaya'],
                'uraian_kejadian'   => $validated['uraian_kejadian'],
                'sumber_kejadian'   => $validated['sumber_kejadian'],
                'tipe'              => $validated['tipe'],
                'foto_bukti'        => $fotoBuktiPath,
                'file_formulir'     => $fileFormulirPath,
            ]);
        } catch (\Exception $e) {
            // Bersihkan file yang sudah terupload jika ada error
            if ($fotoBuktiPath) Storage::disk('public')->delete($fotoBuktiPath);
            if ($fileFormulirPath) Storage::disk('public')->delete($fileFormulirPath);

            return back()->withErrors(['error' => 'Gagal menyimpan laporan. Silakan coba lagi.']);
        }

        return back()->with('success', 'Laporan kecelakaan kerja berhasil dikirim.');
    }

    public function show(LaporanKecelakaan $laporanKecelakaan)
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('manajemen')) {
            // Admin dan manajemen bisa lihat semua laporan
        } elseif ($user->hasRole('koordinator')) {
            // Koordinator hanya bisa lihat laporan dari unit yang sama
            $laporanKecelakaan->load('user');
            $unitKoordinator = $user->unit?->value;
            $unitPelapor     = $laporanKecelakaan->user?->unit?->value;

            $bolehLihat = $unitKoordinator === 'all'
                || ($unitKoordinator && $unitKoordinator === $unitPelapor);

            abort_unless($bolehLihat, 403);
        } elseif ($laporanKecelakaan->user_id !== $user->id) {
            // PJLP hanya bisa lihat laporan milik sendiri
            abort(403);
        }

        return view('laporan-kecelakaan.show', compact('laporanKecelakaan'));
    }

    public function rekap(Request $request)
    {
        $bulan       = (int) $request->get('bulan', now()->month);
        $tahun       = (int) $request->get('tahun', now()->year);
        $search      = $request->get('search', '');
        $tipeFilter  = $request->get('tipe_filter', '');

        $query = LaporanKecelakaan::with('user')
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pelapor', 'like', "%{$search}%")
                  ->orWhere('nama_korban', 'like', "%{$search}%")
                  ->orWhere('tempat', 'like', "%{$search}%");
            });
        }
        if ($tipeFilter) {
            $query->where('tipe', $tipeFilter);
        }

        $logbooks   = $query->paginate(20)->withQueryString();
        $totalEntri = LaporanKecelakaan::byBulan($bulan, $tahun)->count();
        $tipe       = LaporanKecelakaan::TIPE;

        // Stat per tipe
        $statTipe = LaporanKecelakaan::byBulan($bulan, $tahun)
            ->selectRaw('tipe, count(*) as total')
            ->groupBy('tipe')
            ->pluck('total', 'tipe');

        return view('laporan-kecelakaan.rekap', compact(
            'logbooks', 'totalEntri', 'bulan', 'tahun',
            'search', 'tipeFilter', 'tipe', 'statTipe'
        ));
    }
}
