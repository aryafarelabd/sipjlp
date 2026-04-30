<?php

namespace App\Http\Controllers;

use App\Models\LogbookB3;
use App\Models\LogbookB3Foto;
use App\Models\MasterAreaCs;
use App\Models\Pjlp;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookB3Controller extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPjlp()) {
            return $this->pjlpIndex($request, $user);
        }

        return $this->rekapIndex($request);
    }

    // ── PJLP: Form + Riwayat ────────────────────────────────────────

    private function pjlpIndex(Request $request, $user)
    {
        $pjlp = Pjlp::where('user_id', $user->id)->first();

        if (!$pjlp) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun tidak terhubung ke data PJLP. Hubungi administrator.');
        }

        $areas  = MasterAreaCs::active()->orderBy('urutan')->get();
        $shifts = Shift::where('is_active', true)->get();

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $riwayat = LogbookB3::with(['area', 'shift', 'fotosApd', 'fotosTimbangan'])
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('logbook-b3.index', compact(
            'pjlp', 'areas', 'shifts', 'riwayat', 'bulan', 'tahun'
        ));
    }

    // ── Store ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'area_id'          => 'required|exists:master_area_cs,id',
            'shift_id'         => 'required|exists:shifts,id',
            'tanggal'          => 'required|date|before_or_equal:today',
            // Plastik Kuning — semua opsional tapi jika diisi harus numerik
            'pk_lt1'           => 'nullable|numeric|min:0',
            'pk_igd'           => 'nullable|numeric|min:0',
            'pk_lt2'           => 'nullable|numeric|min:0',
            'pk_ok'            => 'nullable|numeric|min:0',
            'pk_lt3'           => 'nullable|numeric|min:0',
            'pk_lt4'           => 'nullable|numeric|min:0',
            'pk_utilitas'      => 'nullable|numeric|min:0',
            'pk_taman'         => 'nullable|numeric|min:0',
            // Safety Box
            'safety_box_asal'  => 'nullable|string|max:255',
            'safety_box_kg'    => 'nullable|numeric|min:0',
            // Limbah Cair
            'cair_asal'        => 'nullable|string|max:255',
            'cair_kg'          => 'nullable|numeric|min:0',
            // Hepafilter
            'hepafilter_asal'  => 'nullable|string|max:255',
            'hepafilter_kg'    => 'nullable|numeric|min:0',
            // Non Infeksius
            'non_infeksius_jenis' => 'nullable|string|max:255',
            'non_infeksius_kg'    => 'nullable|numeric|min:0',
            'catatan'             => 'nullable|string|max:500',
            // Foto
            'foto_apd'            => 'nullable|array|max:5',
            'foto_apd.*'          => 'image|mimes:jpeg,png,jpg|max:10240',
            'foto_timbangan'      => 'nullable|array|max:5',
            'foto_timbangan.*'    => 'image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'foto_apd.max'            => 'Maksimal 5 foto APD.',
            'foto_timbangan.max'      => 'Maksimal 5 foto timbangan.',
        ]);

        $pjlp = Pjlp::where('user_id', Auth::id())->first();

        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $logbook = LogbookB3::create([
                'pjlp_id'  => $pjlp->id,
                'area_id'  => $request->area_id,
                'shift_id' => $request->shift_id,
                'tanggal'  => $request->tanggal,
                // Plastik Kuning
                'pk_lt1'       => $request->pk_lt1       ?: null,
                'pk_igd'       => $request->pk_igd       ?: null,
                'pk_lt2'       => $request->pk_lt2       ?: null,
                'pk_ok'        => $request->pk_ok        ?: null,
                'pk_lt3'       => $request->pk_lt3       ?: null,
                'pk_lt4'       => $request->pk_lt4       ?: null,
                'pk_utilitas'  => $request->pk_utilitas  ?: null,
                'pk_taman'     => $request->pk_taman     ?: null,
                // Safety Box
                'safety_box_asal' => $request->safety_box_asal ?: null,
                'safety_box_kg'   => $request->safety_box_kg   ?: null,
                // Limbah Cair
                'cair_asal' => $request->cair_asal ?: null,
                'cair_kg'   => $request->cair_kg   ?: null,
                // Hepafilter
                'hepafilter_asal' => $request->hepafilter_asal ?: null,
                'hepafilter_kg'   => $request->hepafilter_kg   ?: null,
                // Non Infeksius
                'non_infeksius_jenis' => $request->non_infeksius_jenis ?: null,
                'non_infeksius_kg'    => $request->non_infeksius_kg    ?: null,
                'catatan'             => $request->catatan,
            ]);

            $folder = 'logbook-b3/' . now()->format('Y-m');

            if ($request->hasFile('foto_apd')) {
                foreach ($request->file('foto_apd') as $foto) {
                    LogbookB3Foto::create([
                        'logbook_b3_id' => $logbook->id,
                        'kategori'      => 'apd',
                        'path'          => $foto->store($folder, 'public'),
                    ]);
                }
            }

            if ($request->hasFile('foto_timbangan')) {
                foreach ($request->file('foto_timbangan') as $foto) {
                    LogbookB3Foto::create([
                        'logbook_b3_id' => $logbook->id,
                        'kategori'      => 'timbangan',
                        'path'          => $foto->store($folder, 'public'),
                    ]);
                }
            }
        });

        return redirect()->route('logbook-b3.index')
            ->with('success', 'Logbook B3 berhasil disimpan!');
    }

    // ── Koordinator / Admin: Rekap ───────────────────────────────────

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $areaId = $request->input('area_id');
        $search = $request->input('search');

        $areas = MasterAreaCs::active()->orderBy('urutan')->get();

        $query = LogbookB3::with(['pjlp', 'area', 'shift', 'fotosApd', 'fotosTimbangan'])
            ->byBulan($bulan, $tahun);

        if ($areaId) {
            $query->byArea($areaId);
        }

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        // Statistik agregat bulan ini
        $stats = LogbookB3::byBulan($bulan, $tahun)
            ->when($areaId, fn($q) => $q->byArea($areaId))
            ->selectRaw('
                COUNT(*) as total_entri,
                SUM(COALESCE(pk_lt1,0)+COALESCE(pk_igd,0)+COALESCE(pk_lt2,0)+COALESCE(pk_ok,0)
                    +COALESCE(pk_lt3,0)+COALESCE(pk_lt4,0)+COALESCE(pk_utilitas,0)+COALESCE(pk_taman,0)) as total_pk,
                SUM(COALESCE(safety_box_kg,0))    as total_safety_box,
                SUM(COALESCE(cair_kg,0))          as total_cair,
                SUM(COALESCE(hepafilter_kg,0))    as total_hepafilter,
                SUM(COALESCE(non_infeksius_kg,0)) as total_non_infeksius
            ')
            ->first();

        return view('logbook-b3.rekap', compact(
            'logbooks', 'areas', 'bulan', 'tahun', 'areaId', 'search', 'stats'
        ));
    }
}
