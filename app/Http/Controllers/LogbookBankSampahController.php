<?php

namespace App\Http\Controllers;

use App\Models\LogbookBankSampah;
use App\Models\LogbookBankSampahFoto;
use App\Models\Pjlp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookBankSampahController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('pjlp')) {
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

        $riwayat = LogbookBankSampah::with('fotos')
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        $jenis = LogbookBankSampah::JENIS;

        return view('logbook-bank-sampah.index', compact('pjlp', 'riwayat', 'bulan', 'tahun', 'jenis'));
    }

    public function store(Request $request)
    {
        $jenisRules = collect(array_keys(LogbookBankSampah::JENIS))
            ->mapWithKeys(fn($f) => [$f => 'nullable|numeric|min:0'])
            ->toArray();

        $request->validate(array_merge([
            'tanggal' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'foto'    => 'required|array|min:1|max:5',
            'foto.*'  => 'image|mimes:jpeg,png,jpg|max:10240',
        ], $jenisRules), [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'foto.required'           => 'Bukti dokumentasi wajib diupload.',
            'foto.min'                => 'Minimal 1 foto bukti dokumentasi.',
            'foto.max'                => 'Maksimal 5 foto.',
        ]);

        $pjlp = Pjlp::where('user_id', Auth::id())->first();

        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $data = ['pjlp_id' => $pjlp->id, 'tanggal' => $request->tanggal, 'catatan' => $request->catatan];

            foreach (array_keys(LogbookBankSampah::JENIS) as $field) {
                $data[$field] = $request->filled($field) ? $request->input($field) : null;
            }

            $logbook = LogbookBankSampah::create($data);

            $folder = 'logbook-bank-sampah/' . now()->format('Y-m');
            foreach ($request->file('foto') as $foto) {
                LogbookBankSampahFoto::create([
                    'logbook_bank_sampah_id' => $logbook->id,
                    'path'                   => $foto->store($folder, 'public'),
                ]);
            }
        });

        return redirect()->route('logbook-bank-sampah.index')
            ->with('success', 'Logbook Bank Sampah berhasil disimpan!');
    }

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $search = $request->input('search', '');

        $query = LogbookBankSampah::with(['pjlp', 'fotos'])
            ->byBulan($bulan, $tahun);

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        // Statistik total per jenis bulan ini
        $coalesce = collect(array_keys(LogbookBankSampah::JENIS))
            ->map(fn($f) => "SUM(COALESCE({$f},0)) as total_{$f}")
            ->implode(', ');

        $stats = LogbookBankSampah::byBulan($bulan, $tahun)
            ->selectRaw("COUNT(*) as total_entri, {$coalesce}")
            ->first();

        $totalEntri = $stats->total_entri ?? 0;
        $jenis      = LogbookBankSampah::JENIS;

        return view('logbook-bank-sampah.rekap', compact(
            'logbooks', 'bulan', 'tahun', 'search', 'stats', 'totalEntri', 'jenis'
        ));
    }
}
