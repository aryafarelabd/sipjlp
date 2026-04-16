<?php

namespace App\Http\Controllers;

use App\Models\PatrolInspeksi;
use App\Models\PatrolInspeksiFoto;
use App\Models\Pjlp;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatrolInspeksiController extends Controller
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

        $shifts = Shift::where('is_active', true)->get();
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        $riwayat = PatrolInspeksi::with(['shift', 'fotos'])
            ->where('pjlp_id', $pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        $seksi      = PatrolInspeksi::SEKSI;
        $area       = PatrolInspeksi::AREA;
        $pintuAkses = PatrolInspeksi::PINTU_AKSES;

        return view('patrol-inspeksi.index', compact(
            'pjlp', 'shifts', 'riwayat', 'bulan', 'tahun', 'seksi', 'area', 'pintuAkses'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'      => 'required|date|before_or_equal:today',
            'shift_id'     => 'required|exists:shifts,id',
            'area'         => 'required|in:' . implode(',', array_keys(PatrolInspeksi::AREA)),
            'rekomendasi'  => 'required|string|max:2000',
            'alasan_pintu' => 'nullable|string|max:1000',
            // Foto per seksi
            'foto.*'       => 'nullable|array|max:1',
            'foto.*.*'     => 'image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'rekomendasi.required'    => 'Rekomendasi perbaikan wajib diisi.',
        ]);

        // Validasi foto wajib untuk seksi tertentu
        foreach (PatrolInspeksi::SEKSI as $key => $config) {
            if ($config['foto_required'] && !$request->hasFile("foto.{$key}")) {
                return back()
                    ->withErrors(["foto.{$key}" => "Foto untuk seksi '{$config['label']}' wajib diupload."])
                    ->withInput();
            }
        }

        $pjlp = Pjlp::where('user_id', Auth::id())->first();
        if (!$pjlp) {
            return back()->withErrors(['error' => 'Akun tidak terhubung ke data PJLP.'])->withInput();
        }

        DB::transaction(function () use ($request, $pjlp) {
            $data = [
                'pjlp_id'      => $pjlp->id,
                'shift_id'     => $request->shift_id,
                'tanggal'      => $request->tanggal,
                'area'         => $request->area,
                'rekomendasi'  => $request->rekomendasi,
                'alasan_pintu' => $request->alasan_pintu,
            ];

            // Simpan data checklist per seksi
            foreach (array_keys(PatrolInspeksi::SEKSI) as $seksi) {
                $items = [];
                foreach (array_keys(PatrolInspeksi::SEKSI[$seksi]['items']) as $item) {
                    $items[$item] = $request->boolean("seksi.{$seksi}.{$item}");
                }
                $data[$seksi] = $items;
            }

            // Pintu akses
            $pintu = [];
            foreach (array_keys(PatrolInspeksi::PINTU_AKSES) as $p) {
                $pintu[$p] = $request->boolean("pintu_akses.{$p}");
            }
            $data['pintu_akses'] = $pintu;

            $patrol = PatrolInspeksi::create($data);

            // Simpan foto per seksi
            $folder = 'patrol-inspeksi/' . now()->format('Y-m');
            foreach (array_keys(PatrolInspeksi::SEKSI) as $seksi) {
                if ($request->hasFile("foto.{$seksi}")) {
                    foreach ((array) $request->file("foto.{$seksi}") as $foto) {
                        PatrolInspeksiFoto::create([
                            'patrol_inspeksi_id' => $patrol->id,
                            'seksi'              => $seksi,
                            'path'               => $foto->store($folder, 'public'),
                        ]);
                    }
                }
            }

            // Foto temuan
            if ($request->hasFile('foto_temuan')) {
                PatrolInspeksiFoto::create([
                    'patrol_inspeksi_id' => $patrol->id,
                    'seksi'              => 'temuan',
                    'path'               => $request->file('foto_temuan')->store($folder, 'public'),
                ]);
            }
        });

        return redirect()->route('patrol-inspeksi.index')
            ->with('success', 'Laporan Security Patrol berhasil disimpan!');
    }

    public function show(PatrolInspeksi $patrolInspeksi)
    {
        $patrolInspeksi->load(['pjlp', 'shift', 'fotos']);
        $seksi      = PatrolInspeksi::SEKSI;
        $pintuAkses = PatrolInspeksi::PINTU_AKSES;

        return view('patrol-inspeksi.show', compact('patrolInspeksi', 'seksi', 'pintuAkses'));
    }

    private function rekapIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $area   = $request->input('area', '');
        $search = $request->input('search', '');

        $query = PatrolInspeksi::with(['pjlp', 'shift'])
            ->byBulan($bulan, $tahun);

        if ($area) {
            $query->where('area', $area);
        }

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbooks   = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();
        $totalEntri = PatrolInspeksi::byBulan($bulan, $tahun)->count();
        $areaList   = PatrolInspeksi::AREA;

        return view('patrol-inspeksi.rekap', compact(
            'logbooks', 'bulan', 'tahun', 'area', 'search', 'totalEntri', 'areaList'
        ));
    }
}
