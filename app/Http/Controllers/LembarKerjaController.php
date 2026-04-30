<?php

namespace App\Http\Controllers;

use App\Enums\StatusLembarKerja;
use App\Models\AuditLog;
use App\Models\LembarKerja;
use App\Models\LembarKerjaDetail;
use App\Models\Lokasi;
use App\Http\Requests\AddLembarKerjaDetailRequest;
use App\Http\Requests\RejectLembarKerjaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LembarKerjaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LembarKerja::with(['pjlp', 'validasi']);

        if ($user->isPjlp()) {
            $pjlp = $user->pjlp;
            if (!$pjlp) {
                return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
            }
            $query->forPjlp($pjlp->id);
        } elseif ($user->isKoordinator()) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('tanggal')) {
            $query->forDate($request->tanggal);
        }

        $lembarKerja = $query->latest()->paginate(15);

        return view('lembar-kerja.index', compact('lembarKerja'));
    }

    public function create()
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
        }

        // Check if today's lembar kerja already exists
        $existing = LembarKerja::forPjlp($pjlp->id)->forDate(today())->first();
        if ($existing) {
            return redirect()->route('lembar-kerja.edit', $existing)
                ->with('info', 'Lembar kerja untuk hari ini sudah ada. Silakan lanjutkan mengisi.');
        }

        $lokasi = Lokasi::active()->get();

        return view('lembar-kerja.create', compact('pjlp', 'lokasi'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $lembarKerja = LembarKerja::create([
            'pjlp_id' => $pjlp->id,
            'tanggal' => today(),
            'status' => StatusLembarKerja::DRAFT,
        ]);

        AuditLog::log('Membuat lembar kerja', $lembarKerja, null, $lembarKerja->toArray());

        return redirect()->route('lembar-kerja.edit', $lembarKerja)
            ->with('success', 'Lembar kerja berhasil dibuat. Silakan tambahkan detail pekerjaan.');
    }

    public function show(LembarKerja $lembarKerja)
    {
        $this->authorize('view', $lembarKerja);
        $lembarKerja->load(['pjlp', 'details.lokasi', 'validasi.validator']);

        return view('lembar-kerja.show', compact('lembarKerja'));
    }

    public function edit(LembarKerja $lembarKerja)
    {
        $user = auth()->user();

        if (!$lembarKerja->canBeEdited()) {
            return redirect()->route('lembar-kerja.show', $lembarKerja)
                ->with('error', 'Lembar kerja tidak dapat diedit karena sudah disubmit.');
        }

        if ($user->isPjlp() && $lembarKerja->pjlp_id !== $user->pjlp?->id) {
            abort(403);
        }

        $lembarKerja->load(['details.lokasi']);
        $lokasi = Lokasi::active()->get();

        return view('lembar-kerja.edit', compact('lembarKerja', 'lokasi'));
    }

    public function addDetail(AddLembarKerjaDetailRequest $request, LembarKerja $lembarKerja)
    {
        if (!$lembarKerja->canBeEdited()) {
            return back()->with('error', 'Lembar kerja tidak dapat diedit.');
        }

        $validated = $request->validated();

        // Upload foto
        $fotoPath = $request->file('foto')->store('lembar-kerja', 'public');

        $detail = $lembarKerja->details()->create([
            'jam' => $validated['jam'],
            'pekerjaan' => $validated['pekerjaan'],
            'lokasi_id' => $validated['lokasi_id'],
            'keterangan' => $validated['keterangan'],
            'foto' => basename($fotoPath),
        ]);

        AuditLog::log('Menambah detail lembar kerja', $detail, null, $detail->toArray());

        return back()->with('success', 'Detail pekerjaan berhasil ditambahkan.');
    }

    public function deleteDetail(LembarKerjaDetail $detail)
    {
        $lembarKerja = $detail->lembarKerja;

        if (!$lembarKerja->canBeEdited()) {
            return back()->with('error', 'Lembar kerja tidak dapat diedit.');
        }

        // Delete foto
        if ($detail->foto) {
            Storage::disk('public')->delete('lembar-kerja/' . $detail->foto);
        }

        $dataLama = $detail->toArray();
        $detail->delete();

        AuditLog::log('Menghapus detail lembar kerja', null, $dataLama, null);

        return back()->with('success', 'Detail pekerjaan berhasil dihapus.');
    }

    public function submit(LembarKerja $lembarKerja)
    {
        $user = auth()->user();

        if ($user->isPjlp() && $lembarKerja->pjlp_id !== $user->pjlp?->id) {
            abort(403);
        }

        if (!$lembarKerja->canBeSubmitted()) {
            return back()->with('error', 'Lembar kerja tidak dapat disubmit. Pastikan sudah ada detail pekerjaan.');
        }

        $dataLama = $lembarKerja->toArray();
        $lembarKerja->submit();

        AuditLog::log('Submit lembar kerja', $lembarKerja, $dataLama, $lembarKerja->fresh()->toArray());

        return redirect()->route('lembar-kerja.index')
            ->with('success', 'Lembar kerja berhasil disubmit dan menunggu validasi koordinator.');
    }

    public function validateLK(Request $request, LembarKerja $lembarKerja)
    {
        $this->authorize('validate', $lembarKerja);

        if ($lembarKerja->status !== StatusLembarKerja::SUBMITTED) {
            return back()->with('error', 'Lembar kerja tidak dalam status menunggu validasi.');
        }

        $dataLama = $lembarKerja->toArray();
        $lembarKerja->validate(auth()->user(), $request->catatan);

        AuditLog::log('Memvalidasi lembar kerja', $lembarKerja, $dataLama, $lembarKerja->fresh()->toArray());

        return back()->with('success', 'Lembar kerja berhasil divalidasi.');
    }

    public function reject(RejectLembarKerjaRequest $request, LembarKerja $lembarKerja)
    {
        $this->authorize('validate', $lembarKerja);

        $validated = $request->validated();

        $dataLama = $lembarKerja->toArray();
        $lembarKerja->reject(auth()->user(), $validated['catatan']);

        AuditLog::log('Menolak lembar kerja', $lembarKerja, $dataLama, $lembarKerja->fresh()->toArray());

        return back()->with('success', 'Lembar kerja berhasil ditolak.');
    }
}
