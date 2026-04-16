<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiHydrant extends Model
{
    protected $table = 'inspeksi_hydrant';

    protected $fillable = [
        'pjlp_id', 'shift_id', 'tanggal',
        'siamesse_igd', 'utilitas_1', 'utilitas_2', 'parkir_motor', 'gardu_pln',
        'foto_siamesse_igd', 'foto_utilitas_1', 'foto_utilitas_2', 'foto_parkir_motor', 'foto_gardu_pln',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'siamesse_igd'      => 'array',
        'utilitas_1'        => 'array',
        'utilitas_2'        => 'array',
        'parkir_motor'      => 'array',
        'gardu_pln'         => 'array',
        'foto_siamesse_igd' => 'array',
        'foto_utilitas_1'   => 'array',
        'foto_utilitas_2'   => 'array',
        'foto_parkir_motor' => 'array',
        'foto_gardu_pln'    => 'array',
    ];

    const LOKASI = [
        'siamesse_igd' => 'Hydrant Siamesse Connection IGD',
        'utilitas_1'   => 'Hydrant Box & Pillar Utilitas 1 (Depan Gizi)',
        'utilitas_2'   => 'Hydrant Box & Pillar Utilitas 2 (Depan Laundry)',
        'parkir_motor' => 'Hydrant Box & Pillar Parkir Motor',
        'gardu_pln'    => 'Hydrant Box & Pillar Depan Gardu PLN',
    ];

    const KOMPONEN = [
        'nozzle' => [
            'label'   => 'Nozzle',
            'options' => ['baik' => 'Baik', 'rusak' => 'Rusak'],
        ],
        'selang' => [
            'label'   => 'Selang',
            'options' => ['ada_baik' => 'Ada, Baik', 'ada_buruk' => 'Ada, Buruk', 'tidak_ada' => 'Tidak Ada'],
        ],
        'box' => [
            'label'   => 'Box / Fisik',
            'options' => ['baik' => 'Baik', 'buruk' => 'Buruk'],
        ],
        'pilar' => [
            'label'   => 'Hydrant Pilar',
            'options' => ['ada' => 'Ada', 'tidak' => 'Tidak'],
        ],
        'kunci' => [
            'label'   => 'Kunci',
            'options' => ['ada' => 'Ada', 'tidak' => 'Tidak'],
        ],
        'alarm' => [
            'label'   => 'Alarm',
            'options' => ['baik' => 'Baik', 'tidak_baik' => 'Tidak Baik'],
        ],
        'sistem_kunci' => [
            'label'   => 'Sistem Kunci Hydrant Siamese',
            'options' => ['baik' => 'Baik', 'buruk' => 'Buruk'],
        ],
    ];

    // Nilai "aman" per komponen (untuk menghitung status keseluruhan)
    const NILAI_AMAN = [
        'nozzle'       => 'baik',
        'selang'       => 'ada_baik',
        'box'          => 'baik',
        'pilar'        => 'ada',
        'kunci'        => 'ada',
        'alarm'        => 'baik',
        'sistem_kunci' => 'baik',
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Hitung jumlah lokasi dengan semua komponen aman.
     */
    public function getLokasiAmanAttribute(): int
    {
        $aman = 0;
        foreach (array_keys(self::LOKASI) as $lokasi) {
            $data = $this->$lokasi ?? [];
            $lokasiAman = true;
            foreach (self::NILAI_AMAN as $k => $nilaiAman) {
                if (($data[$k] ?? null) !== $nilaiAman) {
                    $lokasiAman = false;
                    break;
                }
            }
            if ($lokasiAman) $aman++;
        }
        return $aman;
    }

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
