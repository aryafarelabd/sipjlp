<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengawasanProyek extends Model
{
    protected $table = 'pengawasan_proyek';

    protected $fillable = [
        'pjlp_id', 'shift_id', 'tanggal', 'nama_proyek', 'lokasi',
        'lalu_lalang', 'apd', 'penanganan_udara', 'sampah_puing',
        'area_proyek', 'kepatuhan_b3', 'foto',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'lalu_lalang'     => 'array',
        'apd'             => 'array',
        'penanganan_udara'=> 'array',
        'sampah_puing'    => 'array',
        'area_proyek'     => 'array',
        'kepatuhan_b3'    => 'array',
    ];

    const SEKSI = [
        'lalu_lalang' => [
            'label' => 'Lalu Lalang dan Akses',
            'items' => [
                'evakuasi_bebas'         => 'Semua jalan keluar dan jalur evakuasi bebas dari hambatan / tumpukan material / limbah',
                'jalur_tanggap_darurat'  => 'Tim Tanggap Darurat memiliki jalur yang bebas hambatan untuk mengakses area proyek',
                'signage_pintu'          => 'Ada tanda yang dipasang (signage) di pintu masuk proyek untuk menghalangi masuknya orang yang tidak berkepentingan',
                'pintu_palang'           => 'Pintu masuk dan keluar tertutup dan diberi palang / rendel gembok',
            ],
        ],
        'apd' => [
            'label' => 'Alat Pelindung Diri (APD)',
            'items' => [
                'pekerja_apd'    => 'Semua pekerja menggunakan APD yang sesuai',
                'stok_apd'       => 'Tersedia stok APD yang cukup untuk setiap pekerja dan cadangan untuk pengunjung',
                'apd_ketinggian' => 'Penggunaan APD untuk pekerja di ketinggian',
            ],
        ],
        'penanganan_udara' => [
            'label' => 'Penanganan Udara',
            'items' => [
                'bebas_debu'    => 'Lantai dan permukaan horizontal bebas debu',
                'lokasi_terpal' => 'Pada saat pengerjaan lokasi ditutup menggunakan terpal',
                'debu_menyebar' => 'Tidak ada debu yang menyebar di area sekitar konstruksi',
            ],
        ],
        'sampah_puing' => [
            'label' => 'Sampah dan Puing',
            'items' => [
                'troli_tertutup'  => 'Troli pengangkut sampah material dari area konstruksi tertutup',
                'puing_harian'    => 'Puing diangkut dan dibuang setiap hari',
                'jalur_puing'     => 'Jalur pembuangan puing jelas dan aman',
                'bersih_harian'   => 'Dilakukan pembersihan setiap hari di area kerja',
                'bebas_serangga'  => 'Tidak ada serangga atau vektor pengganggu yang terlihat',
            ],
        ],
        'area_proyek' => [
            'label' => 'Area Proyek',
            'items' => [
                'tidak_merokok' => 'Tidak ada bukti adanya kegiatan merokok di sekitar area konstruksi',
            ],
        ],
        'kepatuhan_b3' => [
            'label' => 'Kepatuhan terhadap Limbah B3',
            'items' => [
                'penyimpanan_limbah' => 'Adanya penyimpanan dan pembuangan limbah sesuai aturan',
            ],
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
     * Persentase item yang dijawab YA dari semua seksi.
     */
    public function getPersentaseAttribute(): int
    {
        $total = 0;
        $ya    = 0;

        foreach (array_keys(self::SEKSI) as $seksiKey) {
            $data  = $this->$seksiKey ?? [];
            $items = $data['items'] ?? [];
            foreach ($items as $val) {
                $total++;
                if ($val === 'ya') $ya++;
            }
        }

        return $total > 0 ? (int) round($ya / $total * 100) : 0;
    }

    /**
     * Jumlah item YA dan total item.
     */
    public function getScoreAttribute(): array
    {
        $total = 0;
        $ya    = 0;
        foreach (array_keys(self::SEKSI) as $seksiKey) {
            $data  = $this->$seksiKey ?? [];
            $items = $data['items'] ?? [];
            foreach ($items as $val) {
                $total++;
                if ($val === 'ya') $ya++;
            }
        }
        return ['ya' => $ya, 'total' => $total];
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
