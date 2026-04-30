<?php

namespace App\Http\Controllers;

use App\Models\LogbookLimbah;
use App\Models\LogbookLimbahFoto;
use App\Models\MasterAreaCs;
use App\Models\Pjlp;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookLimbahController extends Controller
{
    /**
     * PJLP: form input + riwayat pribadi
     * Koordinator / Admin: rekap monitoring
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPjlp()) {
            return $this->pjlpIndex($request, $user);
        }

        return $this->rekapIndex($request);
    }

    // ── PJLP View ───────────────────────────────────────────────────

    private function pjlpIndex(Request $request, $user)
    {
        $pjlp = Pjlp::where('user_id', $user->id)->first();

        if (!$pjlp) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun tidak terhubung ke data PJLP. Hubungi administrator.');
        }

        $areas  = MasterAreaCs::active()->orderBy('urutan')->get();
        $shifts = Shift::where('is_active', true)->get();

        // Riwayat logbook milik PJLP ini, bulan ini
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $riwayat = LogbookLimbah::with(['area', 'shift', 'fotosApd', 'fotosTimbangan'])
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('logbook-limbah.index', compact(
            'pjlp', 'areas', 'shifts', 'riwayat', 'bulan', 'tahun'
        ));
    }

    // ── Store (PJLP submit logbook) ─────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'area_id'        => 'required|exists:master_area_cs,id',
            'shift_id'       => 'required|exists:shifts,id',
            'tanggal'        => 'required|date|before_or_equal:today',
            'berat_domestik' => 'required|numeric|min:0',
            'berat_kompos'   => 'required|numeric|min:0',
            'catatan'        => 'nullable|string|max:500',
            'foto_apd'       => 'nullable|array|max:5',
            'foto_apd.*'     => 'image|mimes:jpeg,png,jpg|max:10240',
            'foto_timbangan' => 'nullable|array|max:5',
            'foto_timbangan.*' => 'image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'berat_domestik.min'      => 'Berat domestik tidak boleh negatif.',
            'berat_kompos.min'        => 'Berat kompos tidak boleh negatif.',
            'foto_apd.max'            => 'Maksimal 5 foto APD.',
            'foto_timbangan.max'      => 'Maksimal 5 foto timbangan.',
        ]);

        $pjlp = Pjlp::where('user_id', Auth::id())->first();

        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $logbook = LogbookLimbah::create([
                'pjlp_id'        => $pjlp->id,
                'area_id'        => $request->area_id,
                'shift_id'       => $request->shift_id,
                'tanggal'        => $request->tanggal,
                'berat_domestik' => $request->berat_domestik,
                'berat_kompos'   => $request->berat_kompos,
                'catatan'        => $request->catatan,
            ]);

            $folder = 'logbook-limbah/' . now()->format('Y-m');

            if ($request->hasFile('foto_apd')) {
                foreach ($request->file('foto_apd') as $foto) {
                    $path = $foto->store($folder, 'public');
                    LogbookLimbahFoto::create([
                        'logbook_id' => $logbook->id,
                        'kategori'   => 'apd',
                        'path'       => $path,
                    ]);
                }
            }

            if ($request->hasFile('foto_timbangan')) {
                foreach ($request->file('foto_timbangan') as $foto) {
                    $path = $foto->store($folder, 'public');
                    LogbookLimbahFoto::create([
                        'logbook_id' => $logbook->id,
                        'kategori'   => 'timbangan',
                        'path'       => $path,
                    ]);
                }
            }
        });

        return redirect()->route('logbook-limbah.index')
            ->with('success', 'Logbook berhasil disimpan!');
    }

    // ── Koordinator / Admin: Rekap ───────────────────────────────────

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $areaId = $request->input('area_id');
        $search = $request->input('search');

        $areas = MasterAreaCs::active()->orderBy('urutan')->get();

        $query = LogbookLimbah::with(['pjlp', 'area', 'shift', 'fotosApd', 'fotosTimbangan'])
            ->byBulan($bulan, $tahun);

        if ($areaId) {
            $query->byArea($areaId);
        }

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        // Statistik bulan ini
        $stats = LogbookLimbah::byBulan($bulan, $tahun)
            ->when($areaId, fn($q) => $q->byArea($areaId))
            ->selectRaw('COUNT(*) as total_entri, SUM(berat_domestik) as total_domestik, SUM(berat_kompos) as total_kompos')
            ->first();

        return view('logbook-limbah.rekap', compact(
            'logbooks', 'areas', 'bulan', 'tahun', 'areaId', 'search', 'stats'
        ));
    }
}
