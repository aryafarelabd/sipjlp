<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class InspeksiHydrantIndoor extends Model
{
    protected $table = 'inspeksi_hydrant_indoor';

    protected $fillable = [
        'pjlp_id', 'shift_id', 'tanggal', 'lokasi',
        'hydrant_1', 'hydrant_2',
        'foto_hydrant_1', 'foto_hydrant_2',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'hydrant_1'      => 'array',
        'hydrant_2'      => 'array',
        'foto_hydrant_1' => 'array',
        'foto_hydrant_2' => 'array',
    ];

    const LOKASI = [
        'lantai_1_gizi' => 'Lantai 1 & Gizi',
        'lantai_2'      => 'Lantai 2',
        'lantai_3'      => 'Lantai 3',
        'lantai_4'      => 'Lantai 4',
    ];

    // Komponen yang sama untuk hydrant_1 dan hydrant_2
    const KOMPONEN = [
        'nozzle'    => [
            'label'   => 'Nozzle Hydrant',
            'options' => ['baik' => 'Baik', 'rusak' => 'Rusak'],
        ],
        'selang'    => [
            'label'   => 'Selang Fire Hose',
            'options' => ['ada_baik' => 'Ada, Baik', 'ada_buruk' => 'Ada, Buruk', 'tidak_ada' => 'Tidak Ada'],
            'keterangan' => true,
        ],
        'box'       => [
            'label'   => 'Box / Fisik',
            'options' => ['baik' => 'Baik', 'buruk' => 'Buruk'],
            'keterangan' => true,
        ],
        'alarm'     => [
            'label'   => 'Alarm Break Glass',
            'options' => ['baik' => 'Baik', 'tidak_baik' => 'Tidak Baik'],
        ],
        'hose_rack' => [
            'label'   => 'Hose Rack / Dudukan Selang',
            'options' => ['ada' => 'Ada', 'tidak' => 'Tidak'],
            'required' => false,
        ],
    ];

    const NILAI_AMAN = [
        'nozzle'    => 'baik',
        'selang'    => 'ada_baik',
        'box'       => 'baik',
        'alarm'     => 'baik',
        'hose_rack' => 'ada',
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
     * Cek apakah semua komponen hydrant (1 atau 2) bernilai aman.
     */
    public function isHydrantAman(array $data): bool
    {
        foreach (self::NILAI_AMAN as $k => $v) {
            if (($data[$k] ?? null) !== $v) {
                return false;
            }
        }
        return true;
    }

    /**
     * Jumlah hydrant (dari 2) yang semua komponennya aman.
     */
    public function getHydrantAmanAttribute(): int
    {
        $count = 0;
        if ($this->hydrant_1 && $this->isHydrantAman($this->hydrant_1)) $count++;
        if ($this->hydrant_2 && $this->isHydrantAman($this->hydrant_2)) $count++;
        return $count;
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
