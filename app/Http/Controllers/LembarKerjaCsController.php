<?php

namespace App\Http\Controllers;

use App\Models\LembarKerjaCs;
use App\Models\MasterAreaCs;
use App\Models\MasterKegiatanLkCs;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LembarKerjaCsController extends Controller
{
    // ── Index: form PJLP atau rekap koordinator ───────────────────

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPjlp()) {
            return $this->formPjlp($request);
        }

        return $this->rekap($request);
    }

    private function formPjlp(Request $request)
    {
        $pjlp = Auth::user()->pjlp;
        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);

        $areas    = MasterAreaCs::active()->ordered()->get();
        $shifts   = Shift::where('is_active', true)->orderBy('jam_mulai')->get();
        $periodik = MasterKegiatanLkCs::active()->periodik()->ordered()->get();
        $extraJob = MasterKegiatanLkCs::active()->extraJob()->ordered()->get();

        $riwayat = LembarKerjaCs::with(['area', 'shift'])
            ->byPjlp($pjlp->id)
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal')
            ->get();

        return view('lembar-kerja-cs.index', compact(
            'pjlp', 'areas', 'shifts', 'periodik', 'extraJob',
            'riwayat', 'bulan', 'tahun'
        ));
    }

    public function rekap(Request $request)
    {
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $query = LembarKerjaCs::with(['pjlp', 'area', 'shift', 'validator'])
            ->byBulan($bulan, $tahun)
            ->orderByDesc('tanggal');

        if ($search) {
            $query->whereHas('pjlp', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }
        if ($status) {
            $query->byStatus($status);
        }

        $lembarKerja = $query->paginate(20)->withQueryString();
        $totalEntri  = LembarKerjaCs::byBulan($bulan, $tahun)->count();
        $pending     = LembarKerjaCs::byBulan($bulan, $tahun)->byStatus('submitted')->count();

        return view('lembar-kerja-cs.rekap', compact(
            'lembarKerja', 'totalEntri', 'pending',
            'bulan', 'tahun', 'search', 'status'
        ));
    }

    // ── Store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $pjlp = Auth::user()->pjlp;
        if (!$pjlp) {
            return back()->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $request->validate([
            'tanggal'              => 'required|date|before_or_equal:today',
            'area_id'              => 'required|exists:master_area_cs,id',
            'shift_id'             => 'required|exists:shifts,id',
            'kegiatan_periodik'    => 'required|array|min:3',
            'kegiatan_periodik.*'  => 'required|exists:master_kegiatan_lk_cs,id',
            'kegiatan_extra_job'   => 'nullable|array',
            'kegiatan_extra_job.*' => 'exists:master_kegiatan_lk_cs,id',
            'foto_dokumentasi'     => 'nullable|array|max:20',
            'foto_dokumentasi.*'   => 'image|mimes:jpeg,jpg,png|max:10240',
            'deskripsi_foto'       => 'nullable|string|max:500',
            'catatan'              => 'nullable|string|max:1000',
        ], [
            'kegiatan_periodik.min' => 'Minimal 3 kegiatan periodik harus dipilih.',
        ]);

        // Resolusi ID ke object {id, nama} agar nama tetap tersimpan
        $idsPeriodik = $request->kegiatan_periodik ?? [];
        $idsExtra    = $request->kegiatan_extra_job ?? [];
        $allIds      = array_unique(array_merge($idsPeriodik, $idsExtra));
        $kegMap      = MasterKegiatanLkCs::whereIn('id', $allIds)->pluck('nama', 'id');

        $kegPeriodik = array_map(fn($id) => ['id' => (int)$id, 'nama' => $kegMap[$id] ?? ''], $idsPeriodik);
        $kegExtra    = !empty($idsExtra)
            ? array_map(fn($id) => ['id' => (int)$id, 'nama' => $kegMap[$id] ?? ''], $idsExtra)
            : null;

        // Upload foto
        $fotoPaths = [];
        if ($request->hasFile('foto_dokumentasi')) {
            $folder = 'lembar-kerja-cs/' . now()->format('Y-m');
            foreach ($request->file('foto_dokumentasi') as $foto) {
                $fotoPaths[] = $foto->store($folder, 'public');
            }
        }

        LembarKerjaCs::create([
            'pjlp_id'            => $pjlp->id,
            'area_id'            => $request->area_id,
            'shift_id'           => $request->shift_id,
            'tanggal'            => $request->tanggal,
            'kegiatan_periodik'  => $kegPeriodik,
            'kegiatan_extra_job' => $kegExtra,
            'foto_dokumentasi'   => !empty($fotoPaths) ? $fotoPaths : null,
            'deskripsi_foto'     => $request->deskripsi_foto,
            'catatan'            => $request->catatan,
            'status'             => LembarKerjaCs::STATUS_SUBMITTED,
            'submitted_at'       => now(),
        ]);

        return back()->with('success', 'Lembar kerja berhasil disimpan dan dikirim ke koordinator.');
    }

    // ── Show ──────────────────────────────────────────────────────

    public function show(LembarKerjaCs $lembarKerjaC)
    {
        $user = Auth::user();
        if ($user->isPjlp() && $lembarKerjaC->pjlp_id !== $user->pjlp?->id) {
            abort(403);
        }

        $lembarKerjaC->load(['pjlp', 'area', 'shift', 'validator']);

        return view('lembar-kerja-cs.show', compact('lembarKerjaC'));
    }

    // ── Validate / Reject ─────────────────────────────────────────

    public function validateLk(Request $request, LembarKerjaCs $lembarKerjaC)
    {
        if (!$lembarKerjaC->canValidate()) {
            return back()->with('error', 'Lembar kerja ini tidak bisa divalidasi.');
        }
        $request->validate(['catatan_koordinator' => 'nullable|string|max:500']);
        $lembarKerjaC->validateLk(Auth::id(), $request->catatan_koordinator);

        return back()->with('success', 'Lembar kerja berhasil divalidasi.');
    }

    public function rejectLk(Request $request, LembarKerjaCs $lembarKerjaC)
    {
        if (!$lembarKerjaC->canValidate()) {
            return back()->with('error', 'Lembar kerja ini tidak bisa ditolak.');
        }
        $request->validate(['catatan_koordinator' => 'required|string|max:500']);
        $lembarKerjaC->rejectLk(Auth::id(), $request->catatan_koordinator);

        return back()->with('success', 'Lembar kerja ditolak.');
    }

    // ── Destroy ───────────────────────────────────────────────────

    public function destroy(LembarKerjaCs $lembarKerjaC)
    {
        $user = Auth::user();
        if ($user->isPjlp() && $lembarKerjaC->pjlp_id !== $user->pjlp?->id) {
            abort(403);
        }
        if (!$lembarKerjaC->canEdit()) {
            return back()->with('error', 'Lembar kerja yang sudah divalidasi tidak dapat dihapus.');
        }

        foreach ($lembarKerjaC->foto_dokumentasi ?? [] as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $lembarKerjaC->delete();
        return back()->with('success', 'Lembar kerja berhasil dihapus.');
    }
}
