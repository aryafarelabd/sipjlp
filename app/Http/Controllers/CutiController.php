<?php

namespace App\Http\Controllers;

use App\Enums\StatusCuti;
use App\Enums\UnitType;
use App\Models\AuditLog;
use App\Models\Cuti;
use App\Models\JenisCuti;
use App\Models\Pjlp;
use App\Models\User;
use App\Http\Requests\RejectCutiRequest;
use App\Http\Requests\StoreCutiRequest;
use App\Notifications\CutiDiajukanNotification;
use App\Notifications\CutiDiputuskanNotification;
use Illuminate\Http\Request;

class CutiController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Cuti::with(['pjlp', 'jenisCuti', 'approvedBy', 'approvedByDanru', 'approvedByChief', 'danru']);

        $this->scopeCutiIndex($query, $user);

        $this->applyCutiFilters($query, $request);

        $cuti          = $query->latest()->paginate(15);
        $jenisCutiList = JenisCuti::active()->get();

        return view('cuti.index', compact('cuti', 'jenisCutiList'));
    }

    public function validasi(Request $request)
    {
        abort_unless($request->user()->can('cuti.approve'), 403);

        $query = Cuti::with(['pjlp', 'jenisCuti', 'approvedBy', 'approvedByDanru', 'approvedByChief', 'danru']);

        $this->scopeCutiValidasi($query, $request->user());
        $this->applyCutiFilters($query, $request);

        $cuti = $query->oldest('tanggal_permohonan')->paginate(15);

        return view('cuti.validasi', compact('cuti'));
    }

    public function create()
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan. Hubungi Administrator.');
        }

        $jenisCutiList = JenisCuti::active()->get();

        // Anggota security perlu pilih danru. Danru sendiri langsung ke Chief.
        $danruList = null;
        if ($user->hasRole('pjlp') && !$user->hasAnyRole(['danru', 'chief']) && $pjlp->unit === UnitType::SECURITY) {
            $danruList = Pjlp::whereHas('user', fn($q) => $q->role('danru'))
                ->where('unit', UnitType::SECURITY)
                ->orderBy('nama')
                ->get();
        }

        $approvalInfo = match (true) {
            $user->hasRole('danru') => 'Pengajuan akan dikirim ke Chief → Koordinator secara berjenjang',
            $user->hasRole('chief') => 'Pengajuan akan dikirim ke Koordinator untuk persetujuan akhir',
            $danruList?->isNotEmpty() => 'Pengajuan akan dikirim ke Danru → Chief → Koordinator secara berjenjang',
            default => 'Pengajuan cuti akan dikirim ke Koordinator untuk disetujui',
        };

        return view('cuti.create', compact('pjlp', 'jenisCutiList', 'danruList', 'approvalInfo'));
    }

    public function store(StoreCutiRequest $request)
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
        }

        $validated = $request->validated();

        // Tentukan status awal & danru_id berdasarkan role + unit pengaju
        $danruId = null;
        if ($user->hasRole('danru')) {
            $status = StatusCuti::MENUNGGU_CHIEF;
        } elseif ($user->hasRole('chief') || $user->hasRole('pj_cs')) {
            $status = StatusCuti::MENUNGGU_KOORDINATOR;
        } elseif ($pjlp->unit === UnitType::SECURITY) {
            // Anggota security wajib pilih danru
            $request->validate(['danru_id' => 'required|exists:pjlp,id']);
            $danruId = $request->danru_id;
            $status  = StatusCuti::MENUNGGU_DANRU;
        } elseif ($pjlp->unit === UnitType::CLEANING) {
            // Anggota CS → ke PJ CS dulu
            $status = StatusCuti::MENUNGGU_PJ_CS;
        } else {
            $status = StatusCuti::MENUNGGU;
        }

        $cuti = Cuti::create([
            'pjlp_id'      => $pjlp->id,
            'danru_id'     => $danruId,
            'jenis_cuti_id'=> $validated['jenis_cuti_id'],
            'alasan'       => $validated['alasan'],
            'no_telp'      => $validated['no_telp'],
            'tgl_mulai'    => $validated['tgl_mulai'],
            'tgl_selesai'  => $validated['tgl_selesai'],
            'status'       => $status,
        ]);

        AuditLog::log('Mengajukan cuti', $cuti, null, $cuti->toArray());

        // Notifikasi Telegram berdasarkan level pertama
        $cuti->load('pjlp.user', 'jenisCuti');
        if ($status === StatusCuti::MENUNGGU_DANRU) {
            $danruUser = Pjlp::find($danruId)?->user;
            if ($danruUser?->telegram_chat_id) {
                $danruUser->notify(new CutiDiajukanNotification($cuti));
            }
        } elseif ($status === StatusCuti::MENUNGGU_CHIEF) {
            $chiefs = User::role('chief')->whereNotNull('telegram_chat_id')->get();
            foreach ($chiefs as $c) {
                $c->notify(new CutiDiajukanNotification($cuti));
            }
        } elseif ($status === StatusCuti::MENUNGGU_PJ_CS) {
            $pjCsList = User::role('pj_cs')->whereNotNull('telegram_chat_id')->get();
            foreach ($pjCsList as $pj) {
                $pj->notify(new CutiDiajukanNotification($cuti));
            }
        } else {
            $koordinators = User::role('koordinator')
                ->where('unit', $pjlp->unit)
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($koordinators as $k) {
                $k->notify(new CutiDiajukanNotification($cuti));
            }
        }

        return redirect()->route('cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function show(Cuti $cuti)
    {
        $this->authorize('view', $cuti);

        $cuti->load(['pjlp', 'jenisCuti', 'approvedBy', 'approvedByDanru', 'approvedByChief', 'danru']);

        return view('cuti.show', compact('cuti'));
    }

    public function approve(Request $request, Cuti $cuti)
    {
        $this->authorize('approve', $cuti);

        $user     = auth()->user();
        $dataLama = $cuti->toArray();

        if ($user->hasRole('danru')) {
            $cuti->approveByDanru($user);
            AuditLog::log('Menyetujui cuti (level danru)', $cuti, $dataLama, $cuti->fresh()->toArray());

            // Notif chief
            $chiefs = User::role('chief')->whereNotNull('telegram_chat_id')->get();
            foreach ($chiefs as $c) {
                $c->notify(new CutiDiajukanNotification($cuti->load('pjlp.user', 'jenisCuti')));
            }
            return back()->with('success', 'Cuti disetujui, diteruskan ke Chief.');
        }

        if ($user->hasRole('chief')) {
            $cuti->approveByChief($user);
            AuditLog::log('Menyetujui cuti (level chief)', $cuti, $dataLama, $cuti->fresh()->toArray());

            $cuti->load('pjlp', 'jenisCuti');
            $koordinators = User::role('koordinator')
                ->where('unit', $cuti->pjlp->unit)
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($koordinators as $k) {
                $k->notify(new CutiDiajukanNotification($cuti->load('pjlp.user', 'jenisCuti')));
            }
            return back()->with('success', 'Cuti disetujui, diteruskan ke Koordinator.');
        }

        if ($user->hasRole('pj_cs')) {
            $cuti->approveByPjCs($user);
            AuditLog::log('Menyetujui cuti (level PJ CS)', $cuti, $dataLama, $cuti->fresh()->toArray());

            $cuti->load('pjlp', 'jenisCuti');
            $koordinators = User::role('koordinator')
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($koordinators as $k) {
                $k->notify(new CutiDiajukanNotification($cuti->load('pjlp.user', 'jenisCuti')));
            }
            return back()->with('success', 'Cuti disetujui, diteruskan ke Koordinator CS.');
        }

        // Koordinator / admin: final approval
        $cuti->approve($user);
        AuditLog::log('Menyetujui cuti', $cuti, $dataLama, $cuti->fresh()->toArray());

        $cuti->load('pjlp.user', 'jenisCuti');
        $userPjlp = $cuti->pjlp?->user;
        if ($userPjlp?->telegram_chat_id) {
            $userPjlp->notify(new CutiDiputuskanNotification($cuti->fresh()));
        }

        return back()->with('success', 'Cuti berhasil disetujui.');
    }

    public function reject(RejectCutiRequest $request, Cuti $cuti)
    {
        $this->authorize('approve', $cuti);

        $validated = $request->validated();
        $dataLama  = $cuti->toArray();

        $cuti->reject(auth()->user(), $validated['alasan_penolakan']);
        AuditLog::log('Menolak cuti', $cuti, $dataLama, $cuti->fresh()->toArray());

        // Notifikasi ke PJLP pengaju
        $cuti->load('pjlp.user', 'jenisCuti');
        $userPjlp = $cuti->pjlp?->user;
        if ($userPjlp?->telegram_chat_id) {
            $userPjlp->notify(new CutiDiputuskanNotification($cuti->fresh()));
        }

        return back()->with('success', 'Cuti berhasil ditolak.');
    }

    private function scopeCutiIndex($query, User $user): void
    {
        if ($user->hasRole('admin') || $user->can('cuti.view-all')) {
            return;
        }

        if ($user->hasAnyRole(['pjlp', 'danru', 'chief'])) {
            $pjlp = $user->pjlp;
            if (!$pjlp) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->forPjlp($pjlp->id);
            return;
        }

        if ($user->hasRole('koordinator') || $user->can('cuti.view-unit')) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                if ($user->unit && $user->unit->value !== 'all') {
                    $q->where('unit', $user->unit);
                }
            });
            return;
        }

        $pjlp = $user->pjlp;
        if (!$pjlp) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->forPjlp($pjlp->id);
    }

    private function scopeCutiValidasi($query, User $user): void
    {
        if ($user->hasRole('admin')) {
            $query->pending();
            return;
        }

        if ($user->hasRole('danru')) {
            $query->where('status', StatusCuti::MENUNGGU_DANRU)
                ->where('danru_id', $user->pjlp?->id);
            return;
        }

        if ($user->hasRole('chief')) {
            $query->where('status', StatusCuti::MENUNGGU_CHIEF)
                ->whereHas('pjlp', fn ($q) => $q->where('unit', UnitType::SECURITY));
            return;
        }

        if ($user->hasRole('pj_cs')) {
            $query->where('status', StatusCuti::MENUNGGU_PJ_CS)
                ->whereHas('pjlp', fn ($q) => $q->where('unit', UnitType::CLEANING));
            return;
        }

        if ($user->hasRole('koordinator')) {
            $query->whereIn('status', [StatusCuti::MENUNGGU, StatusCuti::MENUNGGU_KOORDINATOR])
                ->whereHas('pjlp', function ($q) use ($user) {
                    if ($user->unit && $user->unit->value !== 'all') {
                        $q->where('unit', $user->unit);
                    }
                });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function applyCutiFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tgl_mulai', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tgl_selesai', '<=', $request->sampai);
        }
    }
}
