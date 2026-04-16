<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengecekanApar extends Model
{
    protected $table = 'pengecekan_apar';

    protected $fillable = [
        'pjlp_id', 'shift_id', 'tanggal', 'lokasi', 'units', 'keterangan_buruk',
        'berat', 'tekanan', 'kondisi', 'kondisi_ket',
        'pin_segel', 'handle', 'petunjuk', 'segitiga_api',
        'masa_berlaku', 'keterangan_lain', 'foto_bukti',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'masa_berlaku' => 'date',
        'units'        => 'array',
        'foto_bukti'   => 'array',
    ];

    const LOKASI = [
        'lantai_1'        => 'Lantai 1',
        'lantai_2'        => 'Lantai 2',
        'lantai_3'        => 'Lantai 3',
        'lantai_4_rooftop'=> 'Lantai 4 & Rooftop',
        'luar_gedung'     => 'Luar Gedung & Utilitas',
    ];

    const UNITS_PER_LOKASI = [
        'lantai_1' => [
            'panel_lt1'   => '2. Ruang Panel Lt 1 (DCP)',
            'igd'         => '11. Ruang IGD (CO²)',
            'isolasi_igd' => '12. Isolasi IGD (CO²)',
            'poli_bedah'  => '13. Poli Bedah/TB (CO²)',
            'rekam_medik' => '14. Ruang Rekam Medik (CO²)',
            'farmasi'     => '15. Ruang Farmasi (CO²)',
            'ipsrs'       => '16. Ruang IPSRS (CO²)',
        ],
        'lantai_2' => [
            'panel_lt2'   => '3. Ruang Panel Lt 2 (DCP)',
            'ex_cssd'     => '17. Ruang ex CSSD (CO²)',
            'ok'          => '18. Ruangan OK (CO²)',
            'lab'         => '19. Ruang LAB (CO²)',
            'vk'          => '20. Ruangan VK (CO²)',
            'tunggu_lt2'  => '31. Ruang Tunggu Lantai 2 (CO²)',
        ],
        'lantai_3' => [
            'panel_lt3' => '4. Ruang Panel Lt 3 (DCP)',
            'zambrut'   => '21. Ruang Zambrut 4 (CO²)',
            'emeral'    => '22. Ruangan Emeral (CO²)',
            'najandra'  => '23. Ruang Najandra (CO²)',
        ],
        'lantai_4_rooftop' => [
            'panel_lt4'    => '5. Ruang Panel Lt 4 (DCP)',
            'server_lt4'   => '24. Ruang Server Lt 4 (CO²)',
            'aula'         => '25. Ruangan Aula (CO²)',
            'vidya'        => '26. Ruang Vidya (CO²)',
            'tunggu_lt4'   => '27. Ruangan Tunggu Lt 4 (CO²)',
            'gudang_logistik' => '28. Ruangan Gudang Logistik (CO²)',
            'gudang_farmasi'  => '29. Ruangan Gudang Farmasi (CO²)',
            'farras'          => '30. Ruangan Farras (CO²)',
            'mesin_lift'      => '6. Ruang Mesin Lift (CO²) — Rooftop',
        ],
        'luar_gedung' => [
            'pos_security'  => '1. Ruang Pos Security (DCP)',
            'loundry'       => '7. Ruang Loundry (DCP)',
            'dapur_gizi'    => '8. Ruang Dapur Gizi (DCP)',
            'janitor_gizi'  => '9. Ruang Janitor Gizi (DCP)',
            'pompa_utama'   => '10. Ruang Pompa Utama (DCP)',
            'trafo'         => '32. Ruang Trafo APAB (CO²)',
            'panel_utama'   => '33. Ruang Panel Utama APAB (CO²)',
            'genset'        => '34. Ruang Genset APAB (CO²)',
            'tps_b3'        => '35. Ruang TPS B3 (DCP)',
            'depan_igd'     => '36. Depan IGD APAB (DCP)',
            'workshop'      => '37. Ruang Workshop Basment (CO²)',
            'mushollah'     => '38. Ruang Depan Mushollah (DCP)',
        ],
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
     * Jumlah unit yang statusnya 'buruk'.
     */
    public function getJumlahBurukAttribute(): int
    {
        return count(array_filter($this->units ?? [], fn($v) => $v === 'buruk'));
    }

    /**
     * Jumlah total unit di lokasi ini.
     */
    public function getTotalUnitAttribute(): int
    {
        return count(self::UNITS_PER_LOKASI[$this->lokasi] ?? []);
    }

    /**
     * Apakah semua komponen rincian aman.
     */
    public function getRincianAmanAttribute(): bool
    {
        return $this->kondisi === 'baik'
            && $this->pin_segel === 'ada'
            && $this->handle === 'baik'
            && $this->petunjuk === 'ada'
            && $this->segitiga_api === 'ada';
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
