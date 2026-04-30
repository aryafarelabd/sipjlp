<?php

namespace App\Http\Controllers;

use App\Models\InspeksiHydrant;
use App\Models\Pjlp;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspeksiHydrantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPjlp()) {
            return $this->pjlpIndex($request, $user);
        }

        return $this->rekapIndex($request);
    }

    private function pjlpIndex(Request $request, $user)
    {
        $pjlp = Pjlp::where('user_id', $user->id)->first();

        if (!$pjlp) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun tidak terhubung ke data PJLP. Hubungi administrator.');
        }

        $shifts = Shift::where('is_active', true)->get();
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        $riwayat = InspeksiHydrant::with('shift')
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        $lokasi   = InspeksiHydrant::LOKASI;
        $komponen = InspeksiHydrant::KOMPONEN;

        return view('inspeksi-hydrant.index', compact(
            'pjlp', 'shifts', 'riwayat', 'bulan', 'tahun', 'lokasi', 'komponen'
        ));
    }

    public function store(Request $request)
    {
        // Buat validation rules dinamis untuk foto per lokasi
        $fotoRules = [];
        foreach (array_keys(InspeksiHydrant::LOKASI) as $lok) {
            $fotoRules["foto_{$lok}"]    = 'nullable|array|max:3';
            $fotoRules["foto_{$lok}.*"]  = 'image|mimes:jpeg,jpg,png|max:5120';
        }

        $request->validate(array_merge([
            'tanggal'  => 'required|date|before_or_equal:today',
            'shift_id' => 'required|exists:shifts,id',
        ], $fotoRules), [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
        ]);

        $pjlp = Pjlp::where('user_id', Auth::id())->first();
        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        $data = [
            'pjlp_id'  => $pjlp->id,
            'shift_id' => $request->shift_id,
            'tanggal'  => $request->tanggal,
        ];

        foreach (array_keys(InspeksiHydrant::LOKASI) as $lok) {
            $lokasiData = [];
            foreach (array_keys(InspeksiHydrant::KOMPONEN) as $komp) {
                $lokasiData[$komp]          = $request->input("lokasi.{$lok}.{$komp}");
                $lokasiData[$komp . '_ket'] = $request->input("lokasi.{$lok}.{$komp}_ket") ?: null;
            }
            $data[$lok] = $lokasiData;

            // Upload foto per lokasi (max 3)
            $fotoPaths = [];
            if ($request->hasFile("foto_{$lok}")) {
                foreach ($request->file("foto_{$lok}") as $foto) {
                    $fotoPaths[] = $foto->store(
                        'inspeksi-hydrant/' . now()->format('Y-m'),
                        'public'
                    );
                }
            }
            $data["foto_{$lok}"] = !empty($fotoPaths) ? $fotoPaths : null;
        }

        InspeksiHydrant::create($data);

        return redirect()->route('inspeksi-hydrant.index')
            ->with('success', 'Laporan Pemeriksaan Hydrant Outdoor berhasil disimpan!');
    }

    public function show(InspeksiHydrant $inspeksiHydrant)
    {
        $inspeksiHydrant->load(['pjlp', 'shift']);
        $lokasi   = InspeksiHydrant::LOKASI;
        $komponen = InspeksiHydrant::KOMPONEN;
        $nilaiAman = InspeksiHydrant::NILAI_AMAN;

        return view('inspeksi-hydrant.show', compact('inspeksiHydrant', 'lokasi', 'komponen', 'nilaiAman'));
    }

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $search = $request->input('search', '');

        $query = InspeksiHydrant::with(['pjlp', 'shift'])->byBulan($bulan, $tahun);

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks   = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();
        $totalEntri = InspeksiHydrant::byBulan($bulan, $tahun)->count();
        $lokasi     = InspeksiHydrant::LOKASI;
        $komponen   = InspeksiHydrant::KOMPONEN;

        return view('inspeksi-hydrant.rekap', compact(
            'logbooks', 'bulan', 'tahun', 'search', 'totalEntri', 'lokasi', 'komponen'
        ));
    }
}
