<?php

namespace App\Http\Controllers;

use App\Models\PengecekanApar;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengecekanAparController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        if ($user->isPjlp()) {
            $pjlp    = $user->pjlp;
            $shifts  = Shift::orderBy('jam_mulai')->get();
            $riwayat = PengecekanApar::with('shift')
                ->where('pjlp_id', $pjlp->id)
                ->byBulan($bulan, $tahun)
                ->orderByDesc('tanggal')
                ->get();

            return view('pengecekan-apar.index', compact('shifts', 'riwayat', 'bulan', 'tahun'));
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
            'tanggal'      => 'required|date|before_or_equal:today',
            'shift_id'     => 'required|exists:shifts,id',
            'lokasi'       => 'required|in:' . implode(',', array_keys(PengecekanApar::LOKASI)),
            'berat'        => 'nullable|string|max:50',
            'tekanan'      => 'nullable|string|max:50',
            'kondisi'      => 'required|in:baik,buruk',
            'kondisi_ket'  => 'nullable|string',
            'pin_segel'    => 'required|in:ada,tidak',
            'handle'       => 'required|in:baik,buruk',
            'petunjuk'     => 'required|in:ada,tidak',
            'segitiga_api' => 'required|in:ada,tidak',
            'masa_berlaku' => 'required|date',
            'keterangan_lain'    => 'nullable|string',
            'keterangan_buruk'   => 'nullable|string',
            'foto_bukti'         => 'nullable|array|max:3',
            'foto_bukti.*'       => 'image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $lokasi = $validated['lokasi'];
        $unitDefs = PengecekanApar::UNITS_PER_LOKASI[$lokasi] ?? [];

        // Validasi semua unit harus diisi
        $unitsInput = $request->input('units', []);
        $units = [];
        foreach (array_keys($unitDefs) as $unitKey) {
            $val = $unitsInput[$unitKey] ?? null;
            if (!in_array($val, ['baik', 'buruk'])) {
                return back()->withInput()->withErrors([
                    "units.{$unitKey}" => 'Semua unit APAR/APAB wajib dinilai (Baik/Buruk).'
                ]);
            }
            $units[$unitKey] = $val;
        }

        // Upload foto bukti (max 3)
        $fotoPaths = [];
        if ($request->hasFile('foto_bukti')) {
            foreach ($request->file('foto_bukti') as $foto) {
                $fotoPaths[] = $foto->store(
                    'pengecekan-apar/' . now()->format('Y-m'),
                    'public'
                );
            }
        }

        PengecekanApar::create([
            'pjlp_id'          => $pjlp->id,
            'shift_id'         => $validated['shift_id'],
            'tanggal'          => $validated['tanggal'],
            'lokasi'           => $lokasi,
            'units'            => $units,
            'keterangan_buruk' => $validated['keterangan_buruk'] ?? null,
            'berat'            => $validated['berat'] ?? null,
            'tekanan'          => $validated['tekanan'] ?? null,
            'kondisi'          => $validated['kondisi'],
            'kondisi_ket'      => $validated['kondisi_ket'] ?? null,
            'pin_segel'        => $validated['pin_segel'],
            'handle'           => $validated['handle'],
            'petunjuk'         => $validated['petunjuk'],
            'segitiga_api'     => $validated['segitiga_api'],
            'masa_berlaku'     => $validated['masa_berlaku'],
            'keterangan_lain'  => $validated['keterangan_lain'] ?? null,
            'foto_bukti'       => !empty($fotoPaths) ? $fotoPaths : null,
        ]);

        return back()->with('success', 'Laporan pengecekan APAR & APAB berhasil disimpan.');
    }

    public function show(PengecekanApar $pengecekanApar)
    {
        $pengecekanApar->load('pjlp', 'shift');
        $unitDefs = PengecekanApar::UNITS_PER_LOKASI[$pengecekanApar->lokasi] ?? [];
        $lokasi   = PengecekanApar::LOKASI;

        return view('pengecekan-apar.show', compact('pengecekanApar', 'unitDefs', 'lokasi'));
    }

    public function rekap(Request $request)
    {
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');
        $lokasiFilter = $request->get('lokasi_filter', '');

        $query = PengecekanApar::with('pjlp', 'shift')
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal');

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }
        if ($lokasiFilter) {
            $query->where('lokasi', $lokasiFilter);
        }

        $logbooks   = $query->paginate(25)->withQueryString();
        $totalEntri = PengecekanApar::byBulan($bulan, $tahun)->count();
        $lokasi     = PengecekanApar::LOKASI;

        return view('pengecekan-apar.rekap', compact(
            'logbooks', 'totalEntri', 'bulan', 'tahun', 'search', 'lokasiFilter', 'lokasi'
        ));
    }
}
