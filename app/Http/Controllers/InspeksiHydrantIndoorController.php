<?php

namespace App\Http\Controllers;

use App\Models\InspeksiHydrantIndoor;
use App\Models\Pjlp;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InspeksiHydrantIndoorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        // PJLP → form input
        if ($user->isPjlp()) {
            $pjlp   = $user->pjlp;
            $shifts  = Shift::orderBy('jam_mulai')->get();
            $riwayat = InspeksiHydrantIndoor::with('shift')
                ->where('pjlp_id', $pjlp->id)
                ->byBulan($bulan, $tahun)
                ->orderByDesc('tanggal')
                ->get();

            return view('inspeksi-hydrant-indoor.index', compact(
                'shifts', 'riwayat', 'bulan', 'tahun',
            ));
        }

        // Koordinator / Admin → rekap
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
            'tanggal'          => 'required|date|before_or_equal:today',
            'shift_id'         => 'required|exists:shifts,id',
            'lokasi'           => 'required|in:' . implode(',', array_keys(InspeksiHydrantIndoor::LOKASI)),
            'foto_hydrant_1'   => 'nullable|array|max:3',
            'foto_hydrant_1.*' => 'image|mimes:jpeg,jpg,png|max:5120',
            'foto_hydrant_2'   => 'nullable|array|max:3',
            'foto_hydrant_2.*' => 'image|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Validasi komponen per hydrant
        $hydrants = [];
        foreach (['hydrant_1', 'hydrant_2'] as $h) {
            $data = $request->input($h, []);
            // Required komponen
            foreach (InspeksiHydrantIndoor::KOMPONEN as $k => $cfg) {
                $isRequired = $cfg['required'] ?? true;
                if ($isRequired && empty($data[$k])) {
                    return back()
                        ->withInput()
                        ->withErrors(["{$h}.{$k}" => "Kolom {$cfg['label']} pada " . strtoupper(str_replace('_', ' ', $h)) . " wajib diisi."]);
                }
            }
            $hydrants[$h] = [
                'nozzle'      => $data['nozzle'] ?? null,
                'selang'      => $data['selang'] ?? null,
                'selang_ket'  => $data['selang_ket'] ?? null,
                'box'         => $data['box'] ?? null,
                'box_ket'     => $data['box_ket'] ?? null,
                'alarm'       => $data['alarm'] ?? null,
                'hose_rack'   => $data['hose_rack'] ?? null,
                'keterangan'  => $data['keterangan'] ?? null,
            ];
        }

        $fotoData = [];
        foreach (['hydrant_1', 'hydrant_2'] as $h) {
            $paths = [];
            if ($request->hasFile("foto_{$h}")) {
                foreach ($request->file("foto_{$h}") as $foto) {
                    $paths[] = $foto->store('inspeksi-hydrant-indoor/' . now()->format('Y-m'), 'public');
                }
            }
            $fotoData["foto_{$h}"] = !empty($paths) ? $paths : null;
        }

        InspeksiHydrantIndoor::create([
            'pjlp_id'        => $pjlp->id,
            'shift_id'       => $validated['shift_id'],
            'tanggal'        => $validated['tanggal'],
            'lokasi'         => $validated['lokasi'],
            'hydrant_1'      => $hydrants['hydrant_1'],
            'hydrant_2'      => $hydrants['hydrant_2'],
            'foto_hydrant_1' => $fotoData['foto_hydrant_1'],
            'foto_hydrant_2' => $fotoData['foto_hydrant_2'],
        ]);

        return back()->with('success', 'Laporan pemeriksaan hydrant indoor berhasil disimpan.');
    }

    public function show(InspeksiHydrantIndoor $inspeksiHydrantIndoor)
    {
        $inspeksiHydrantIndoor->load('pjlp', 'shift');
        $komponen  = InspeksiHydrantIndoor::KOMPONEN;
        $nilaiAman = InspeksiHydrantIndoor::NILAI_AMAN;
        $lokasi    = InspeksiHydrantIndoor::LOKASI;

        return view('inspeksi-hydrant-indoor.show', compact(
            'inspeksiHydrantIndoor', 'komponen', 'nilaiAman', 'lokasi'
        ));
    }

    public function rekap(Request $request)
    {
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');

        $query = InspeksiHydrantIndoor::with('pjlp', 'shift')
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal');

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks   = $query->paginate(20)->withQueryString();
        $totalEntri = InspeksiHydrantIndoor::byBulan($bulan, $tahun)->count();
        $lokasi     = InspeksiHydrantIndoor::LOKASI;

        return view('inspeksi-hydrant-indoor.rekap', compact(
            'logbooks', 'totalEntri', 'bulan', 'tahun', 'search', 'lokasi'
        ));
    }
}
