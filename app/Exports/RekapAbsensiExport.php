<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Pjlp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?string $unitFilter = null
    ) {}

    public function collection()
    {
        $pjlpQuery = Pjlp::active()->with('user')->orderBy('nama');
        if ($this->unitFilter) {
            $pjlpQuery->unit($this->unitFilter);
        }
        return $pjlpQuery->get();
    }

    public function map($pjlp): array
    {
        $absensiList = Absensi::where('pjlp_id', $pjlp->id)
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->with('shift')
            ->get();

        $hadir       = $absensiList->whereIn('status.value', ['hadir', 'terlambat'])->count();
        $alpha       = $absensiList->where('status.value', 'alpha')->count();
        $izinCuti    = $absensiList->whereIn('status.value', ['izin', 'cuti'])->count();
        $telatMenit  = $absensiList->where('status.value', 'terlambat')->sum('menit_terlambat');

        $pulangCepatMenit = 0;
        foreach ($absensiList as $abs) {
            if (!$abs->shift) continue;
            $tgl          = Carbon::parse($abs->tanggal);
            $shiftSelesai = Carbon::parse($tgl->format('Y-m-d') . ' ' . Carbon::parse($abs->shift->jam_selesai)->format('H:i:s'));
            $shiftMulai   = Carbon::parse($tgl->format('Y-m-d') . ' ' . Carbon::parse($abs->shift->jam_mulai)->format('H:i:s'));
            if ($shiftSelesai->lte($shiftMulai)) $shiftSelesai->addDay();
            if ($abs->jam_masuk && !$abs->jam_pulang) {
                $pulangCepatMenit += 225;
            } elseif ($abs->jam_masuk && $abs->jam_pulang) {
                $jamPulang = Carbon::parse($abs->jam_pulang);
                $selisih   = (int) $jamPulang->diffInMinutes($shiftSelesai, false);
                if ($selisih > 0) $pulangCepatMenit += $selisih;
            }
        }

        return [
            $pjlp->nama,
            $pjlp->nip ?? '-',
            ucfirst($pjlp->unit->value),
            $hadir,
            $alpha,
            $izinCuti,
            $telatMenit,
            $pulangCepatMenit,
        ];
    }

    public function headings(): array
    {
        $bulanLabel = Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
        return [
            "REKAP ABSENSI SELFIE — {$bulanLabel}",
            '', '', '', '', '', '', '',
        ];
    }

    public function title(): string
    {
        return 'Rekap ' . Carbon::create($this->tahun, $this->bulan)->format('M Y');
    }

    public function styles(Worksheet $sheet): array
    {
        // Add a proper header row after the title row
        $sheet->insertNewRowBefore(2, 1);
        $sheet->fromArray([
            ['Nama PJLP', 'NIP', 'Unit', 'Hadir (hari)', 'Alpha (hari)', 'Izin/Cuti (hari)', 'Telat (menit)', 'Pulang Cepat (menit)']
        ], null, 'A2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9E1F2']]],
        ];
    }
}
