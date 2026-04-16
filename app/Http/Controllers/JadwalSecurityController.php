<?php

namespace App\Http\Controllers;

use App\Enums\UnitType;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Lokasi;
use App\Models\Pjlp;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalSecurityController extends Controller
{
    /** Sama dengan CS — shared setting `jadwal_window_override`. */
    private function editWindow(?int $requestBulan = null, ?int $requestTahun = null): ?array
    {
        $override = AppSetting::get('jadwal_window_override', 'auto');

        if ($override === 'closed') {
            return null;
        }

        if ($override === 'open') {
            return [
                'bulan'  => $requestBulan ?? now()->month,
                'tahun'  => $requestTahun ?? now()->year,
                'reason' => 'window dibuka manual oleh Admin',
                'override' => 'open',
            ];
        }

        $today = now();
        $day   = $today->day;

        if ($day >= 25) {
            $target = $today->copy()->addMonth();
            return [
                'bulan'  => (int) $target->month,
                'tahun'  => (int) $target->year,
                'reason' => 'input jadwal bulan depan (tanggal 25–akhir bulan)',
                'override' => 'auto',
            ];
        }

        if ($day <= 5) {
            $target = $today->copy()->subMonth();
            return [
                'bulan'  => (int) $target->month,
                'tahun'  => (int) $target->year,
                'reason' => 'revisi jadwal bulan lalu (tanggal 1–5)',
                'override' => 'auto',
            ];
        }

        return null;
    }

    /**
     * Tampilan kalender jadwal shift Security per PJLP per bulan.
     */
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $startDate    = Carbon::create($tahun, $bulan, 1);
        $daysInMonth  = $startDate->daysInMonth;

        $pjlps = Pjlp::active()
            ->unit(UnitType::SECURITY)
            ->orderBy('nama')
            ->get();

        // Group jadwal by "pjlp_id_YYYY-MM-DD"
        $jadwals = Jadwal::with(['shift', 'lokasi'])
            ->whereIn('pjlp_id', $pjlps->pluck('id'))
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->keyBy(fn ($j) => $j->pjlp_id . '_' . Carbon::parse($j->tanggal)->format('Y-m-d'));

        $shifts  = Shift::where('is_active', true)->get();
        $lokasis = Lokasi::active()->orderBy('nama')->get();

        $dates = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date      = Carbon::create($tahun, $bulan, $day);
            $dates[]   = [
                'date'      => $date,
                'day'       => $day,
                'dayName'   => $date->translatedFormat('D'),
                'isWeekend' => $date->isWeekend(),
                'isSunday'  => $date->isSunday(),
                'isToday'   => $date->isToday(),
            ];
        }

        $isPublished = Jadwal::whereIn('pjlp_id', $pjlps->pluck('id'))
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('is_published', true)
            ->exists();

        $window     = $this->editWindow($bulan, $tahun);
        $canEdit    = $window && $window['bulan'] === $bulan && $window['tahun'] === $tahun;
        $windowInfo = $window;

        return view('jadwal-security.index', compact(
            'bulan', 'tahun', 'daysInMonth',
            'dates', 'pjlps', 'jadwals',
            'shifts', 'lokasis', 'isPublished',
            'canEdit', 'windowInfo'
        ));
    }

    /**
     * Update satu cell jadwal via AJAX.
     */
    public function update(Request $request)
    {
        $request->validate([
            'pjlp_id'  => 'required|exists:pjlp,id',
            'tanggal'  => 'required|date',
            'shift_id' => 'nullable|exists:shifts,id',
            'lokasi_id'=> 'nullable|exists:lokasi,id',
        ]);

        abort_unless(auth()->user()->can('jadwal.manage'), 403);

        $tanggal = Carbon::parse($request->tanggal);
        $window  = $this->editWindow($tanggal->month, $tanggal->year);
        if (!$window) {
            return response()->json(['success' => false, 'message' => 'Di luar window input jadwal. Jadwal hanya bisa diubah pada tanggal 25–akhir bulan (untuk bulan depan) atau tanggal 1–5 (untuk revisi bulan lalu).'], 403);
        }

        if ($request->filled('shift_id')) {
            $jadwal = Jadwal::firstOrNew(
                ['pjlp_id' => $request->pjlp_id, 'tanggal' => $request->tanggal]
            );
            if (!$jadwal->exists) {
                $jadwal->created_by = auth()->id();
            }
            $jadwal->shift_id     = $request->shift_id;
            $jadwal->lokasi_id    = $request->lokasi_id ?? null;
            $jadwal->is_published = $jadwal->is_published ?? false;
            $jadwal->save();
            $jadwal->load('shift');

            AuditLog::log('Update jadwal security', $jadwal, null, $jadwal->toArray());

            $shift      = $jadwal->shift;
            $displayText = $shift ? strtoupper($shift->nama) : '-';
            $bgColor     = $this->shiftBgColor($shift?->nama);
            $textColor   = $this->shiftTextColor($shift?->nama);

            return response()->json([
                'success'      => true,
                'display_text' => $displayText,
                'bg_color'     => $bgColor,
                'text_color'   => $textColor,
            ]);
        }

        // shift_id null = hapus jadwal
        $deleted = Jadwal::where('pjlp_id', $request->pjlp_id)
            ->whereDate('tanggal', $request->tanggal)
            ->delete();

        if ($deleted) {
            AuditLog::log('Hapus jadwal security', null, null, [
                'pjlp_id' => $request->pjlp_id,
                'tanggal' => $request->tanggal,
            ]);
        }

        return response()->json(['success' => true, 'display_text' => '-', 'bg_color' => '', 'text_color' => '']);
    }

    /**
     * Publish / unpublish semua jadwal bulan ini agar tampil di PJLP.
     */
    public function publish(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        abort_unless(auth()->user()->can('jadwal.manage'), 403);

        $pjlpIds = Pjlp::active()->unit(UnitType::SECURITY)->pluck('id');

        $count = Jadwal::whereIn('pjlp_id', $pjlpIds)
            ->whereYear('tanggal', $request->tahun)
            ->whereMonth('tanggal', $request->bulan)
            ->update(['is_published' => true]);

        AuditLog::log("Publish jadwal security {$request->bulan}/{$request->tahun} ({$count} entri)");

        return response()->json([
            'success' => true,
            'message' => "{$count} jadwal berhasil dipublikasikan.",
        ]);
    }

    /**
     * Copy semua jadwal dari satu tanggal ke beberapa tanggal target.
     */
    public function copyFromDate(Request $request)
    {
        $request->validate([
            'source_date'  => 'required|date',
            'target_dates' => 'required|array|min:1',
            'target_dates.*' => 'date',
        ]);

        abort_unless(auth()->user()->can('jadwal.manage'), 403);

        $firstTarget = Carbon::parse($request->target_dates[0] ?? null);
        $window      = $this->editWindow($firstTarget->month, $firstTarget->year);
        if (!$window) {
            return response()->json(['success' => false, 'message' => 'Di luar window input jadwal.'], 403);
        }

        $sourceJadwals = Jadwal::whereDate('tanggal', $request->source_date)
            ->whereHas('pjlp', fn ($q) => $q->unit(UnitType::SECURITY))
            ->get();

        if ($sourceJadwals->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada jadwal di tanggal sumber.'], 400);
        }

        DB::transaction(function () use ($sourceJadwals, $request) {
            foreach ($request->target_dates as $targetDate) {
                foreach ($sourceJadwals as $src) {
                    Jadwal::updateOrCreate(
                        ['pjlp_id' => $src->pjlp_id, 'tanggal' => $targetDate],
                        [
                            'shift_id'    => $src->shift_id,
                            'lokasi_id'   => $src->lokasi_id,
                            'is_published'=> false,
                            'created_by'  => auth()->id(),
                        ]
                    );
                }
            }
        });

        $count = $sourceJadwals->count() * count($request->target_dates);
        AuditLog::log("Copy jadwal security dari {$request->source_date} ({$count} entri)");

        return response()->json(['success' => true, 'message' => "{$count} jadwal berhasil disalin."]);
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function shiftBgColor(?string $nama): string
    {
        return match (strtolower((string) $nama)) {
            'pagi'  => '#cce5ff',
            'siang' => '#fff3cd',
            'malam' => '#f8c8dc',
            default => '#667382',
        };
    }

    private function shiftTextColor(?string $nama): string
    {
        return match (strtolower((string) $nama)) {
            'pagi'  => '#004085',
            'siang' => '#856404',
            'malam' => '#721c47',
            default => '#fff',
        };
    }
}
