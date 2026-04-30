<?php

namespace App\Http\Controllers;

use App\Models\LogbookDekontaminasi;
use App\Models\LogbookDekontaminasiFoto;
use App\Models\Pjlp;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookDekontaminasiController extends Controller
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

        $riwayat = LogbookDekontaminasi::with(['shift', 'fotos'])
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('logbook-dekontaminasi.index', compact('pjlp', 'shifts', 'riwayat', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'  => 'required|date|before_or_equal:today',
            'shift_id' => 'required|exists:shifts,id',
            'lokasi'   => 'required|string|max:1000',
            'catatan'  => 'nullable|string|max:500',
            'foto'     => 'required|array|min:1|max:5',
            'foto.*'   => 'image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'foto.required'           => 'Bukti kegiatan (foto) wajib diupload.',
            'foto.min'                => 'Minimal 1 foto bukti kegiatan.',
            'foto.max'                => 'Maksimal 5 foto.',
            'lokasi.required'         => 'Lokasi/ruangan wajib diisi.',
        ]);

        $pjlp = Pjlp::where('user_id', Auth::id())->first();

        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $logbook = LogbookDekontaminasi::create([
                'pjlp_id'  => $pjlp->id,
                'shift_id' => $request->shift_id,
                'tanggal'  => $request->tanggal,
                'lokasi'   => $request->lokasi,
                'catatan'  => $request->catatan,
            ]);

            $folder = 'logbook-dekontaminasi/' . now()->format('Y-m');
            foreach ($request->file('foto') as $foto) {
                LogbookDekontaminasiFoto::create([
                    'logbook_dekontaminasi_id' => $logbook->id,
                    'path'                     => $foto->store($folder, 'public'),
                ]);
            }
        });

        return redirect()->route('logbook-dekontaminasi.index')
            ->with('success', 'Logbook Dekontaminasi Udara berhasil disimpan!');
    }

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $search = $request->input('search', '');

        $query = LogbookDekontaminasi::with(['pjlp', 'shift', 'fotos'])
            ->byBulan($bulan, $tahun);

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks   = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();
        $totalEntri = LogbookDekontaminasi::byBulan($bulan, $tahun)->count();

        return view('logbook-dekontaminasi.rekap', compact(
            'logbooks', 'bulan', 'tahun', 'search', 'totalEntri'
        ));
    }
}
