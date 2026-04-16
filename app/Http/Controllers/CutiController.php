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

        if ($user->hasRole('pjlp') || $user->hasRole('danru') || $user->hasRole('chief')) {
            $pjlp = $user->pjlp;
            if (!$pjlp) {
                return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan.');
            }

            if ($user->hasRole('danru')) {
                // Danru: cuti milik sendiri + cuti yang ditujukan ke danru ini
                $query->where(function ($q) use ($pjlp) {
                    $q->where('pjlp_id', $pjlp->id)
                      ->orWhere('danru_id', $pjlp->id);
                });
            } elseif ($user->hasRole('chief')) {
                // Chief: cuti semua security + cuti milik sendiri
                $query->whereHas('pjlp', fn($q) => $q->where('unit', UnitType::SECURITY));
            } else {
                $query->forPjlp($pjlp->id);
            }
        } elseif ($user->hasRole('koordinator')) {
            $query->whereHas('pjlp', function ($q) use ($user) {
                $q->forKoordinator($user);
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter tanggal
        if ($request->filled('dari')) {
            $query->whereDate('tgl_mulai', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tgl_selesai', '<=', $request->sampai);
        }

        $cuti          = $query->latest()->paginate(15);
        $jenisCutiList = JenisCuti::active()->get();

        return view('cuti.index', compact('cuti', 'jenisCutiList'));
    }

    public function create()
    {
        $user = auth()->user();
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return redirect()->route('dashboard')->with('error', 'Profil PJLP tidak ditemukan. Hubungi Administrator.');
        }

        $jenisCutiList = JenisCuti::active()->get();

        // Anggota security (role pjlp, unit security) perlu pilih danru
        $danruList = null;
        if ($user->hasRole('pjlp') && $pjlp->unit === UnitType::SECURITY) {
            $danruList = Pjlp::whereHas('user', fn($q) => $q->role('danru'))
                ->where('unit', UnitType::SECURITY)
                ->orderBy('nama')
                ->get();
        }

        return view('cuti.create', compact('pjlp', 'jenisCutiList', 'danruList'));
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
        } elseif ($user->hasRole('chief')) {
            $status = StatusCuti::MENUNGGU_KOORDINATOR;
        } elseif ($pjlp->unit === UnitType::SECURITY) {
            // Anggota security wajib pilih danru
            $request->validate(['danru_id' => 'required|exists:pjlp,id']);
            $danruId = $request->danru_id;
            $status  = StatusCuti::MENUNGGU_DANRU;
        } else {
            // CS: langsung ke koordinator
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
            // Notif ke danru yang dipilih
            $danruUser = Pjlp::find($danruId)?->user;
            if ($danruUser?->telegram_chat_id) {
                $danruUser->notify(new CutiDiajukanNotification($cuti));
            }
        } elseif ($status === StatusCuti::MENUNGGU_CHIEF) {
            $chiefs = User::role('chief')->whereNotNull('telegram_chat_id')->get();
            foreach ($chiefs as $c) {
                $c->notify(new CutiDiajukanNotification($cuti));
            }
        } else {
            // CS / menunggu_koordinator: notif ke koordinator unit
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
        $user = auth()->user();

        // Check access
        if ($user->hasRole('pjlp') && $cuti->pjlp_id !== $user->pjlp?->id) {
            abort(403);
        }

        if ($user->hasRole('koordinator')) {
            $pjlp = $cuti->pjlp;
            if ($user->unit && $user->unit->value !== 'all' && $pjlp->unit->value !== $user->unit->value) {
                abort(403);
            }
        }

        $cuti->load(['pjlp', 'jenisCuti', 'approvedBy']);

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

            // Notif koordinator
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
}
