<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\JadwalShiftCs;
use App\Models\Pjlp;
use App\Models\Shift;
use Carbon\Carbon;
use App\Http\Requests\BulkUpdateJadwalShiftCsRequest;
use App\Http\Requests\CopyJadwalShiftCsRequest;
use App\Http\Requests\UpdateJadwalShiftCsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalShiftCsController extends Controller
{
    /**
     * Hitung window edit yang diizinkan hari ini.
     * Return: ['bulan' => int, 'tahun' => int, 'reason' => string, 'override' => string|null]
     *         atau null jika di luar window.
     *
     * Jika override = 'open'   → semua bulan bisa diedit (kembalikan bulan yang sedang dibuka).
     * Jika override = 'closed' → null (paksa tutup).
     * Jika override = 'auto'   → ikuti aturan tanggal.
     */
    private function editWindow(?int $requestBulan = null, ?int $requestTahun = null): ?array
    {
        $override = AppSetting::get('jadwal_window_override', 'auto');

        if ($override === 'closed') {
            return null;
        }

        if ($override === 'open') {
            // Paksa buka: bulan yang sedang dilihat bisa diedit
            $bulan = $requestBulan ?? now()->month;
            $tahun = $requestTahun ?? now()->year;
            return [
                'bulan'    => $bulan,
                'tahun'    => $tahun,
                'reason'   => 'window dibuka manual oleh Admin',
                'override' => 'open',
            ];
        }

        // Auto: ikuti aturan tanggal
        $today = now();
        $day   = $today->day;

        if ($day >= 25) {
            $target = $today->copy()->addMonth();
            return [
                'bulan'    => (int) $target->month,
                'tahun'    => (int) $target->year,
                'reason'   => 'input jadwal bulan depan (tanggal 25–akhir bulan)',
                'override' => 'auto',
            ];
        }

        if ($day <= 5) {
            $target = $today->copy()->subMonth();
            return [
                'bulan'    => (int) $target->month,
                'tahun'    => (int) $target->year,
                'reason'   => 'revisi jadwal bulan lalu (tanggal 1–5)',
                'override' => 'auto',
            ];
        }

        return null;
    }

    /**
     * Tampilan jadwal shift per PJLP per tanggal (format tabel kalender)
     */
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Generate tanggal untuk bulan ini
        $startDate = Carbon::create($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Ambil semua PJLP Cleaning yang aktif
        $pjlps = Pjlp::active()
            ->unit(\App\Enums\UnitType::CLEANING)
            ->orderBy('nama')
            ->get();

        // Ambil jadwal shift yang sudah ada (tanpa filter area)
        $jadwals = JadwalShiftCs::with(['shift'])
            ->byBulan($bulan, $tahun)
            ->get()
            ->groupBy(function ($item) {
                return $item->pjlp_id . '_' . $item->tanggal->format('Y-m-d');
            });

        $shifts = Shift::where('is_active', true)->get();

        $isPublished = JadwalShiftCs::whereIn('pjlp_id', $pjlps->pluck('id'))
            ->byBulan($bulan, $tahun)
            ->where('is_published', true)
            ->exists();

        // Generate array tanggal
        $dates = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($tahun, $bulan, $day);
            $dates[] = [
                'date' => $date,
                'day' => $day,
                'dayName' => $date->translatedFormat('D'),
                'isWeekend' => $date->isWeekend(),
                'isSunday' => $date->isSunday(),
                'isToday' => $date->isToday(),
            ];
        }

        $window  = $this->editWindow((int)$bulan, (int)$tahun);
        $canEdit = $window && $window['bulan'] === (int)$bulan && $window['tahun'] === (int)$tahun;
        $windowInfo = $window;

        return view('jadwal-shift-cs.index', compact(
            'bulan', 'tahun', 'daysInMonth',
            'dates', 'pjlps', 'jadwals',
            'shifts', 'isPublished',
            'canEdit', 'windowInfo'
        ));
    }

    /**
     * Update jadwal shift via AJAX
     */
    public function update(UpdateJadwalShiftCsRequest $request)
    {
        $tanggal = Carbon::parse($request->tanggal);
        $window  = $this->editWindow($tanggal->month, $tanggal->year);
        if (!$window) {
            return response()->json(['success' => false, 'message' => 'Di luar window input jadwal. Jadwal hanya bisa diubah pada tanggal 25–akhir bulan (untuk bulan depan) atau tanggal 1–5 (untuk revisi bulan lalu).'], 403);
        }

        $jadwal = JadwalShiftCs::updateOrCreate(
            [
                'pjlp_id' => $request->pjlp_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'shift_id' => $request->status === 'normal' ? $request->shift_id : null,
                'status' => $request->status,
                'updated_by' => auth()->id(),
            ]
        );

        // Jika baru dibuat, set created_by
        if ($jadwal->wasRecentlyCreated) {
            $jadwal->created_by = auth()->id();
            $jadwal->save();
        }

        // Refresh untuk memastikan accessor berjalan dengan data terbaru
        $jadwal->refresh();
        $jadwal->load('shift');

        AuditLog::log('Update jadwal shift CS', $jadwal, null, $jadwal->toArray());

        return response()->json([
            'success' => true,
            'jadwal' => $jadwal,
            'display_text' => $jadwal->display_text,
            'display_color' => $jadwal->display_color,
            'display_color_hex' => $jadwal->display_color_hex,
        ]);
    }

    /**
     * Bulk update jadwal (untuk copy ke beberapa tanggal)
     */
    public function bulkUpdate(BulkUpdateJadwalShiftCsRequest $request)
    {
        if ($request->filled('jadwals')) {
            $firstDate = Carbon::parse($request->jadwals[0]['tanggal'] ?? null);
            $window    = $this->editWindow($firstDate->month, $firstDate->year);
            if (!$window) {
                return response()->json(['success' => false, 'message' => 'Di luar window input jadwal.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            foreach ($request->jadwals as $data) {
                JadwalShiftCs::updateOrCreate(
                    [
                        'area_id' => $request->area_id,
                        'pjlp_id' => $data['pjlp_id'],
                        'tanggal' => $data['tanggal'],
                    ],
                    [
                        'shift_id' => $data['status'] === 'normal' ? $data['shift_id'] : null,
                        'status' => $data['status'],
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            DB::commit();

            AuditLog::log('Bulk update jadwal shift CS');

            return response()->json(['success' => true, 'message' => 'Jadwal berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Copy jadwal dari tanggal tertentu ke tanggal lain
     */
    public function copyFromDate(CopyJadwalShiftCsRequest $request)
    {
        $firstTarget = Carbon::parse($request->target_dates[0] ?? null);
        $window      = $this->editWindow($firstTarget->month, $firstTarget->year);
        if (!$window) {
            return response()->json(['success' => false, 'message' => 'Di luar window input jadwal.'], 403);
        }

        $query = JadwalShiftCs::byTanggal($request->source_date);
        if ($request->filled('area_id')) {
            $query->byArea($request->area_id);
        }
        $sourceJadwals = $query->get();

        if ($sourceJadwals->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada jadwal di tanggal sumber'], 400);
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->target_dates as $targetDate) {
                foreach ($sourceJadwals as $source) {
                    JadwalShiftCs::updateOrCreate(
                        [
                            'pjlp_id' => $source->pjlp_id,
                            'tanggal' => $targetDate,
                        ],
                        [
                            'shift_id'   => $source->shift_id,
                            'status'     => $source->status,
                            'updated_by' => auth()->id(),
                        ]
                    );
                    $count++;
                }
            }

            DB::commit();

            AuditLog::log("Copy jadwal shift CS ({$count} entri)");

            return response()->json(['success' => true, 'message' => "{$count} jadwal berhasil disalin"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Publikasikan semua jadwal CS bulan ini.
     */
    public function publish(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        $pjlpIds = Pjlp::active()->unit(\App\Enums\UnitType::CLEANING)->pluck('id');

        $count = JadwalShiftCs::whereIn('pjlp_id', $pjlpIds)
            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->update(['is_published' => true]);

        AuditLog::log("Publish jadwal shift CS {$request->bulan}/{$request->tahun} ({$count} entri)");

        return response()->json([
            'success' => true,
            'message' => "{$count} jadwal berhasil dipublikasikan.",
        ]);
    }

    /**
     * Buka/tutup paksa window input jadwal — hanya koordinator & admin.
     */
    public function setOverride(Request $request)
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'koordinator']),
            403, 'Hanya koordinator dan admin yang dapat membuka/menutup jadwal secara paksa.'
        );

        $request->validate(['override' => 'required|in:auto,open,closed']);

        $old = AppSetting::get('jadwal_window_override', 'auto');
        AppSetting::set('jadwal_window_override', $request->override);

        $label = match($request->override) {
            'open'   => 'Paksa Buka',
            'closed' => 'Paksa Tutup',
            default  => 'Otomatis',
        };

        AuditLog::log("Ubah jadwal_window_override: {$old} → {$request->override}");

        return back()->with('success', "Window jadwal berhasil diubah ke: {$label}.");
    }

    /**
     * Rekapitulasi jadwal
     */
    public function rekap(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $areaId = $request->input('area_id');

        $areas = MasterAreaCs::active()->orderBy('urutan')->get();

        if (!$areaId && $areas->isNotEmpty()) {
            $areaId = $areas->first()->id;
        }

        $rekap = DB::table('jadwal_shift_cs')
            ->join('pjlp', 'jadwal_shift_cs.pjlp_id', '=', 'pjlp.id')
            ->leftJoin('shifts', 'jadwal_shift_cs.shift_id', '=', 'shifts.id')
            ->select(
                'pjlp.id as pjlp_id',
                'pjlp.nama',
                'pjlp.nip',
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "normal" THEN 1 END) as total_kerja'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "libur" THEN 1 END) as total_libur'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "libur_hari_raya" THEN 1 END) as total_hari_raya'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "cuti" THEN 1 END) as total_cuti'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "izin" THEN 1 END) as total_izin'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "sakit" THEN 1 END) as total_sakit'),
                DB::raw('COUNT(CASE WHEN jadwal_shift_cs.status = "alpha" THEN 1 END) as total_alpha')
            )
            ->where('jadwal_shift_cs.area_id', $areaId)
            ->whereMonth('jadwal_shift_cs.tanggal', $bulan)
            ->whereYear('jadwal_shift_cs.tanggal', $tahun)
            ->groupBy('pjlp.id', 'pjlp.nama', 'pjlp.nip')
            ->orderBy('pjlp.nama')
            ->get();

        return view('jadwal-shift-cs.rekap', compact('areas', 'areaId', 'bulan', 'tahun', 'rekap'));
    }
}
