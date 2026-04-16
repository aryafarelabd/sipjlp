<?php

namespace App\Http\Controllers;

use App\Models\PengawasanProyek;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengawasanProyekController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        if ($user->hasRole('pjlp')) {
            $pjlp    = $user->pjlp;
            $shifts  = Shift::orderBy('jam_mulai')->get();
            $riwayat = PengawasanProyek::with('shift')
                ->where('pjlp_id', $pjlp->id)
                ->byBulan($bulan, $tahun)
                ->orderByDesc('tanggal')
                ->get();

            return view('pengawasan-proyek.index', compact('shifts', 'riwayat', 'bulan', 'tahun'));
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

        $validated = $request->validate([
            'tanggal'     => 'required|date|before_or_equal:today',
            'shift_id'    => 'required|exists:shifts,id',
            'nama_proyek' => 'required|string|max:255',
            'lokasi'      => 'required|string|max:255',
            'foto'        => 'nullable|image|max:10240',
        ]);

        // Validasi setiap seksi: semua item wajib diisi + upaya_perbaikan wajib
        $seksiData = [];
        foreach (PengawasanProyek::SEKSI as $seksiKey => $seksiConfig) {
            $inputSeksi = $request->input($seksiKey, []);
            $items      = [];

            foreach (array_keys($seksiConfig['items']) as $itemKey) {
                $val = $inputSeksi['items'][$itemKey] ?? null;
                if (!in_array($val, ['ya', 'tidak'])) {
                    return back()->withInput()->withErrors([
                        "{$seksiKey}.items.{$itemKey}" =>
                            "Semua item pada seksi \"{$seksiConfig['label']}\" wajib diisi."
                    ]);
                }
                $items[$itemKey] = $val;
            }

            $upaya = trim($inputSeksi['upaya_perbaikan'] ?? '');
            if ($upaya === '') {
                return back()->withInput()->withErrors([
                    "{$seksiKey}.upaya_perbaikan" =>
                        "Upaya perbaikan pada seksi \"{$seksiConfig['label']}\" wajib diisi."
                ]);
            }

            $seksiData[$seksiKey] = [
                'items'            => $items,
                'upaya_perbaikan'  => $upaya,
            ];
        }

        // Simpan foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store(
                'pengawasan-proyek/' . now()->format('Y-m'),
                'public'
            );
        }

        PengawasanProyek::create([
            'pjlp_id'          => $pjlp->id,
            'shift_id'         => $validated['shift_id'],
            'tanggal'          => $validated['tanggal'],
            'nama_proyek'      => $validated['nama_proyek'],
            'lokasi'           => $validated['lokasi'],
            'foto'             => $fotoPath,
            ...$seksiData,
        ]);

        return back()->with('success', 'Laporan pengawasan proyek berhasil disimpan.');
    }

    public function show(PengawasanProyek $pengawasanProyek)
    {
        $pengawasanProyek->load('pjlp', 'shift');
        $seksi = PengawasanProyek::SEKSI;

        return view('pengawasan-proyek.show', compact('pengawasanProyek', 'seksi'));
    }

    public function rekap(Request $request)
    {
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');

        $query = PengawasanProyek::with('pjlp', 'shift')
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pjlp', fn($q2) => $q2->where('nama', 'like', "%{$search}%"))
                  ->orWhere('nama_proyek', 'like', "%{$search}%");
            });
        }

        $logbooks   = $query->paginate(20)->withQueryString();
        $totalEntri = PengawasanProyek::byBulan($bulan, $tahun)->count();

        return view('pengawasan-proyek.rekap', compact(
            'logbooks', 'totalEntri', 'bulan', 'tahun', 'search'
        ));
    }
}
