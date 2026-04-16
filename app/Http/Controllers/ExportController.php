<?php

namespace App\Http\Controllers;

use App\Models\InspeksiHydrant;
use App\Models\InspeksiHydrantIndoor;
use App\Models\LembarKerjaCs;
use App\Models\LogbookB3;
use App\Models\LogbookBankSampah;
use App\Models\LogbookDekontaminasi;
use App\Models\LogbookHepafilter;
use App\Models\LogbookLimbah;
use App\Models\MasterAreaCs;
use App\Models\PatrolInspeksi;
use App\Models\PengecekanApar;
use App\Models\PengawasanProyek;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportController extends Controller
{
    // ── Shared helpers ────────────────────────────────────────────

    private function bulanTahun(Request $r): array
    {
        return [
            (int) $r->get('bulan', now()->month),
            (int) $r->get('tahun', now()->year),
        ];
    }

    private function bulanLabel(int $bulan, int $tahun): string
    {
        return Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');
    }

    private function respond(Request $r, string $title, array $headings, array $rows, string $format): mixed
    {
        $format = strtolower($format);
        $safe   = preg_replace('/[^a-z0-9_-]/i', '_', $title);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.pdf.table', compact('title', 'headings', 'rows'))
                ->setPaper('a4', 'landscape');
            return $pdf->download("{$safe}.pdf");
        }

        // default: xlsx
        $export = new class($headings, $rows, $title) implements FromArray, WithHeadings, WithStyles, WithTitle {
            public function __construct(
                private array $headings,
                private array $rows,
                private string $sheetTitle,
            ) {}

            public function array(): array  { return $this->rows; }
            public function headings(): array { return $this->headings; }
            public function title(): string   { return mb_substr($this->sheetTitle, 0, 31); }

            public function styles(Worksheet $sheet): array
            {
                return [1 => ['font' => ['bold' => true]]];
            }
        };

        return Excel::download($export, "{$safe}.xlsx");
    }

    // ── CS: Lembar Kerja ─────────────────────────────────────────

    public function lembarKerjaCs(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Lembar Kerja CS - ' . $this->bulanLabel($bulan, $tahun);

        $rows = LembarKerjaCs::with(['pjlp', 'area', 'shift'])
            ->byBulan($bulan, $tahun)
            ->orderBy('tanggal')
            ->get()
            ->map(fn($lk) => [
                $lk->tanggal->format('d/m/Y'),
                $lk->pjlp->nama ?? '-',
                $lk->area->nama ?? '-',
                $lk->shift->nama ?? '-',
                count($lk->kegiatan_periodik ?? []),
                count($lk->kegiatan_extra_job ?? []),
                $lk->status_label,
            ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Area', 'Shift', 'Jml Periodik', 'Jml Extra Job', 'Status',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── CS: Logbook Limbah ───────────────────────────────────────

    public function logbookLimbah(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Logbook Limbah Domestik - ' . $this->bulanLabel($bulan, $tahun);

        $query = LogbookLimbah::with(['pjlp', 'area', 'shift'])->byBulan($bulan, $tahun);
        if ($request->filled('area_id')) $query->byArea($request->area_id);

        $rows = $query->orderBy('tanggal')->get()->map(fn($r) => [
            $r->tanggal->format('d/m/Y'),
            $r->pjlp->nama ?? '-',
            $r->area->nama ?? '-',
            $r->shift->nama ?? '-',
            $r->berat_domestik ?? 0,
            $r->berat_kompos ?? 0,
            $r->catatan ?? '',
        ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Area', 'Shift', 'Berat Domestik (kg)', 'Berat Kompos (kg)', 'Catatan',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── CS: Logbook B3 ───────────────────────────────────────────

    public function logbookB3(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Logbook Limbah B3 - ' . $this->bulanLabel($bulan, $tahun);

        $query = LogbookB3::with(['pjlp', 'area', 'shift'])->byBulan($bulan, $tahun);
        if ($request->filled('area_id')) $query->byArea($request->area_id);

        $rows = $query->orderBy('tanggal')->get()->map(fn($r) => [
            $r->tanggal->format('d/m/Y'),
            $r->pjlp->nama ?? '-',
            $r->area->nama ?? '-',
            $r->shift->nama ?? '-',
            $r->safety_box_kg ?? 0,
            $r->cair_kg ?? 0,
            $r->hepafilter_kg ?? 0,
            $r->non_infeksius_kg ?? 0,
            $r->catatan ?? '',
        ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Area', 'Shift',
            'Safety Box (kg)', 'Limbah Cair (kg)', 'Hepafilter (kg)', 'Non Infeksius (kg)', 'Catatan',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── CS: Logbook Hepafilter ───────────────────────────────────

    public function logbookHepafilter(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Logbook Cleaning Hepafilter - ' . $this->bulanLabel($bulan, $tahun);

        $ruangan = LogbookHepafilter::RUANGAN;

        $rows = LogbookHepafilter::with('pjlp')->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(function ($r) use ($ruangan) {
                $base = [$r->tanggal->format('d/m/Y'), $r->pjlp->nama ?? '-'];
                foreach (array_keys($ruangan) as $field) {
                    $base[] = $r->$field ? '✓' : '-';
                }
                $base[] = $r->catatan ?? '';
                return $base;
            })->toArray();

        $headers = ['Tanggal', 'Petugas'];
        foreach ($ruangan as $label) $headers[] = $label;
        $headers[] = 'Catatan';

        return $this->respond($request, $title, $headers, $rows, $request->get('format', 'xlsx'));
    }

    // ── CS: Logbook Dekontaminasi ────────────────────────────────

    public function logbookDekontaminasi(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Logbook Dekontaminasi - ' . $this->bulanLabel($bulan, $tahun);

        $rows = LogbookDekontaminasi::with(['pjlp', 'shift'])->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(fn($r) => [
                $r->tanggal->format('d/m/Y'),
                $r->pjlp->nama ?? '-',
                $r->shift->nama ?? '-',
                $r->lokasi ?? '-',
                $r->catatan ?? '',
            ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Shift', 'Lokasi', 'Catatan',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── CS: Logbook Bank Sampah ──────────────────────────────────

    public function logbookBankSampah(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Logbook Bank Sampah - ' . $this->bulanLabel($bulan, $tahun);

        $jenis = LogbookBankSampah::JENIS;

        $rows = LogbookBankSampah::with('pjlp')->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(function ($r) use ($jenis) {
                $base = [$r->tanggal->format('d/m/Y'), $r->pjlp->nama ?? '-'];
                foreach (array_keys($jenis) as $field) {
                    $base[] = $r->$field ?? 0;
                }
                $base[] = $r->catatan ?? '';
                return $base;
            })->toArray();

        $headers = ['Tanggal', 'Petugas'];
        foreach ($jenis as $label) $headers[] = $label . ' (kg)';
        $headers[] = 'Catatan';

        return $this->respond($request, $title, $headers, $rows, $request->get('format', 'xlsx'));
    }

    // ── Security: Patrol Inspeksi ────────────────────────────────

    public function patrolInspeksi(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Rekap Patrol Inspeksi - ' . $this->bulanLabel($bulan, $tahun);

        $query = PatrolInspeksi::with(['pjlp', 'shift'])->byBulan($bulan, $tahun);
        if ($request->filled('area')) $query->where('area', $request->area);

        $rows = $query->orderBy('tanggal')->get()->map(fn($r) => [
            $r->tanggal->format('d/m/Y'),
            $r->pjlp->nama ?? '-',
            $r->shift->nama ?? '-',
            PatrolInspeksi::AREA[$r->area] ?? $r->area,
            $r->rekomendasi ?? '',
        ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Shift', 'Area', 'Rekomendasi',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── Security: Inspeksi Hydrant Outdoor ───────────────────────

    public function inspeksiHydrant(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Rekap Inspeksi Hydrant Outdoor - ' . $this->bulanLabel($bulan, $tahun);

        $lokasi    = InspeksiHydrant::LOKASI;
        $komponen  = InspeksiHydrant::KOMPONEN;

        $rows = InspeksiHydrant::with(['pjlp', 'shift'])->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(function ($r) use ($lokasi, $komponen) {
                $base = [
                    $r->tanggal->format('d/m/Y'),
                    $r->pjlp->nama ?? '-',
                    $r->shift->nama ?? '-',
                ];
                foreach (array_keys($lokasi) as $field) {
                    $data = $r->$field ?? [];
                    $buruk = collect($data)->filter(fn($v) => $v === 'buruk')->count();
                    $base[] = $buruk > 0 ? "{$buruk} item buruk" : 'Baik';
                }
                return $base;
            })->toArray();

        $headers = ['Tanggal', 'Petugas', 'Shift'];
        foreach ($lokasi as $label) $headers[] = $label;

        return $this->respond($request, $title, $headers, $rows, $request->get('format', 'xlsx'));
    }

    // ── Security: Inspeksi Hydrant Indoor ────────────────────────

    public function inspeksiHydrantIndoor(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Rekap Inspeksi Hydrant Indoor - ' . $this->bulanLabel($bulan, $tahun);

        $rows = InspeksiHydrantIndoor::with(['pjlp', 'shift'])->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(fn($r) => [
                $r->tanggal->format('d/m/Y'),
                $r->pjlp->nama ?? '-',
                $r->shift->nama ?? '-',
                InspeksiHydrantIndoor::LOKASI[$r->lokasi] ?? $r->lokasi,
                $this->hydrantStatus($r->hydrant_1),
                $this->hydrantStatus($r->hydrant_2),
            ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Shift', 'Lokasi', 'Hydrant 1', 'Hydrant 2',
        ], $rows, $request->get('format', 'xlsx'));
    }

    private function hydrantStatus(?array $data): string
    {
        if (empty($data)) return '-';
        $buruk = collect($data)->filter(fn($v) => $v === 'buruk')->count();
        return $buruk > 0 ? "{$buruk} item buruk" : 'Baik';
    }

    // ── Security: Pengecekan APAR ────────────────────────────────

    public function pengecekanApar(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Rekap Pengecekan APAR & APAB - ' . $this->bulanLabel($bulan, $tahun);

        $query = PengecekanApar::with(['pjlp', 'shift'])->byBulan($bulan, $tahun);
        if ($request->filled('lokasi_filter')) $query->where('lokasi', $request->lokasi_filter);

        $rows = $query->orderBy('tanggal')->get()->map(fn($r) => [
            $r->tanggal->format('d/m/Y'),
            $r->pjlp->nama ?? '-',
            $r->shift->nama ?? '-',
            PengecekanApar::LOKASI[$r->lokasi] ?? $r->lokasi,
            $r->units ? implode(', ', $r->units) : '-',
            $r->kondisi ?? '-',
            $r->masa_berlaku?->format('d/m/Y') ?? '-',
        ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Shift', 'Lokasi', 'Unit', 'Kondisi', 'Masa Berlaku',
        ], $rows, $request->get('format', 'xlsx'));
    }

    // ── Security: Pengawasan Proyek ──────────────────────────────

    public function pengawasanProyek(Request $request)
    {
        [$bulan, $tahun] = $this->bulanTahun($request);
        $title = 'Rekap Pengawasan Proyek - ' . $this->bulanLabel($bulan, $tahun);

        $rows = PengawasanProyek::with(['pjlp', 'shift'])->byBulan($bulan, $tahun)->orderBy('tanggal')->get()
            ->map(fn($r) => [
                $r->tanggal->format('d/m/Y'),
                $r->pjlp->nama ?? '-',
                $r->shift->nama ?? '-',
                $r->nama_proyek ?? '-',
                $r->lokasi ?? '-',
            ])->toArray();

        return $this->respond($request, $title, [
            'Tanggal', 'Petugas', 'Shift', 'Nama Proyek', 'Lokasi',
        ], $rows, $request->get('format', 'xlsx'));
    }
}
