<?php

namespace App\Http\Controllers;

use App\Models\LogbookHepafilter;
use App\Models\LogbookHepafilterFoto;
use App\Models\Pjlp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookHepafilterController extends Controller
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

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $riwayat = LogbookHepafilter::with('fotos')
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        $ruangan = LogbookHepafilter::RUANGAN;

        return view('logbook-hepafilter.index', compact('pjlp', 'riwayat', 'bulan', 'tahun', 'ruangan'));
    }

    public function store(Request $request)
    {
        $ruanganRules = collect(array_keys(LogbookHepafilter::RUANGAN))
            ->mapWithKeys(fn($f) => [$f => 'boolean'])
            ->toArray();

        $request->validate(array_merge([
            'tanggal'  => 'required|date|before_or_equal:today',
            'catatan'  => 'nullable|string|max:500',
            'foto.*'   => 'image|mimes:jpeg,png,jpg|max:10240',
            'foto'     => 'nullable|array|max:10',
        ], $ruanganRules), [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'foto.max'                => 'Maksimal 10 foto.',
        ]);

        // Pastikan minimal 1 ruangan dipilih
        $adaRuangan = collect(array_keys(LogbookHepafilter::RUANGAN))
            ->some(fn($f) => $request->boolean($f));

        if (!$adaRuangan) {
            return back()->withErrors(['ruangan' => 'Pilih minimal satu ruangan yang dibersihkan.'])->withInput();
        }

        $pjlp = Pjlp::where('user_id', Auth::id())->first();

        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $data = ['pjlp_id' => $pjlp->id, 'tanggal' => $request->tanggal, 'catatan' => $request->catatan];

            foreach (array_keys(LogbookHepafilter::RUANGAN) as $field) {
                $data[$field] = $request->boolean($field);
            }

            $logbook = LogbookHepafilter::create($data);

            if ($request->hasFile('foto')) {
                $folder = 'logbook-hepafilter/' . now()->format('Y-m');
                foreach ($request->file('foto') as $foto) {
                    LogbookHepafilterFoto::create([
                        'logbook_hepafilter_id' => $logbook->id,
                        'path'                  => $foto->store($folder, 'public'),
                    ]);
                }
            }
        });

        return redirect()->route('logbook-hepafilter.index')
            ->with('success', 'Logbook Cleaning Hepafilter berhasil disimpan!');
    }

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $search = $request->input('search', '');

        $query = LogbookHepafilter::with(['pjlp', 'fotos'])
            ->byBulan($bulan, $tahun);

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        // Hitung total cleaning per ruangan bulan ini
        $statsRuangan = [];
        foreach (array_keys(LogbookHepafilter::RUANGAN) as $field) {
            $statsRuangan[$field] = LogbookHepafilter::byBulan($bulan, $tahun)->where($field, true)->count();
        }

        $totalEntri = LogbookHepafilter::byBulan($bulan, $tahun)->count();
        $ruangan    = LogbookHepafilter::RUANGAN;

        return view('logbook-hepafilter.rekap', compact(
            'logbooks', 'bulan', 'tahun', 'search', 'statsRuangan', 'totalEntri', 'ruangan'
        ));
    }
}
