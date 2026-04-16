<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrolInspeksi extends Model
{
    protected $table = 'patrol_inspeksi';

    protected $fillable = [
        'pjlp_id', 'shift_id', 'tanggal', 'area',
        'tangga_darurat', 'ramp_evakuasi', 'koridor_lantai',
        'pencahayaan', 'jendela', 'penyimpanan_berkas',
        'ruang_kantor', 'pembuangan_sampah', 'tabung_oksigen',
        'bahan_berbahaya', 'bahaya_listrik', 'toilet', 'papan_codered',
        'pintu_akses', 'alasan_pintu', 'rekomendasi',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'tangga_darurat'    => 'array',
        'ramp_evakuasi'     => 'array',
        'koridor_lantai'    => 'array',
        'pencahayaan'       => 'array',
        'jendela'           => 'array',
        'penyimpanan_berkas'=> 'array',
        'ruang_kantor'      => 'array',
        'pembuangan_sampah' => 'array',
        'tabung_oksigen'    => 'array',
        'bahan_berbahaya'   => 'array',
        'bahaya_listrik'    => 'array',
        'toilet'            => 'array',
        'papan_codered'     => 'array',
        'pintu_akses'       => 'array',
    ];

    // ── Konstanta ────────────────────────────────────────────────────

    const AREA = [
        'lantai_1'       => 'Lantai 1',
        'lantai_2'       => 'Lantai 2',
        'lantai_3'       => 'Lantai 3',
        'lantai_4'       => 'Lantai 4 & Rooftop',
        'parkir'         => 'Seluruh Area Parkir (Motor, Mobil dan Empang)',
        'utilitas'       => 'Utilitas (Gizi, Laundry, CSSD, KMJ, Genset, Listrik, Groundtank)',
    ];

    const SEKSI = [
        'tangga_darurat' => [
            'label'        => 'Tangga Darurat',
            'foto_required'=> false,
            'items'        => [
                'bebas_hambatan'        => 'Bebas hambatan',
                'permukaan_tidak_licin' => 'Permukaan tangga tidak licin',
                'keramik_baik'          => 'Keramik utuh/baik',
                'hand_rel_baik'         => 'Tangga dan hand rel dalam kondisi baik',
                'pintu_darurat_ditutup' => 'Pintu darurat ditutup',
                'kondisi_bersih'        => 'Kondisi bersih dan tidak berdebu',
            ],
        ],
        'ramp_evakuasi' => [
            'label'        => 'Ramp / Jalur Evakuasi Pasien / Safety Sign',
            'foto_required'=> true,
            'items'        => [
                'bebas_hambatan'        => 'Bebas hambatan',
                'permukaan_tidak_licin' => 'Permukaan Jalur ramp tidak licin',
                'keramik_baik'          => 'Keramik utuh/baik',
                'hand_rel_baik'         => 'Tangga dan hand rel dalam kondisi baik',
                'pintu_darurat_ditutup' => 'Pintu darurat Ramp ditutup',
                'kondisi_bersih'        => 'Kondisi bersih dan tidak berdebu',
                'lampu_emergency'       => 'Terdapat lampu emergency di jalur evakuasi',
                'rambu_evakuasi'        => 'Rambu / Sign Evakuasi lengkap',
            ],
        ],
        'koridor_lantai' => [
            'label'        => 'Koridor dan Lantai',
            'foto_required'=> false,
            'items'        => [
                'bebas_penghalang' => 'Bebas dari gangguan/penghalang',
                'lantai_utuh'      => 'Lantai utuh/tidak rusak',
                'kondisi_baik'     => 'Dalam kondisi baik',
                'tidak_licin'      => 'Tidak licin',
            ],
        ],
        'pencahayaan' => [
            'label'        => 'Pencahayaan',
            'foto_required'=> false,
            'items'        => [
                'mencukupi'       => 'Pencahayaan mencukupi',
                'warna_alami'     => 'Warna lampu alami',
                'tidak_silau'     => 'Pencahayaan tidak silau',
                'lampu_bersih'    => 'Lampu dan stop kontak bersih',
                'lampu_emergency' => 'Lampu emergency berfungsi baik',
            ],
        ],
        'jendela' => [
            'label'        => 'Jendela',
            'foto_required'=> false,
            'items'        => [
                'tertutup'     => 'Jendela tertutup',
                'kondisi_baik' => 'Jendela dalam kondisi baik',
                'bersih'       => 'Daun jendela bersih tidak berdebu',
            ],
        ],
        'penyimpanan_berkas' => [
            'label'        => 'Penyimpanan Berkas & Barang',
            'foto_required'=> false,
            'items'        => [
                'tempat_mencukupi' => 'Tempat penyimpanan barang mencukupi',
                'barang_rapi'      => 'Barang-barang tersimpan rapi',
                'akses_memadai'    => 'Akses memadai',
                'tersedia_tangga'  => 'Tersedia tangga untuk mengambil barang di tempat tinggi',
                'rak_bersih'       => 'Rak-rak penyimpanan tidak berdebu dan kotor',
            ],
        ],
        'ruang_kantor' => [
            'label'        => 'Ruang Kantor',
            'foto_required'=> false,
            'items'        => [
                'kursi_ergonomis'      => 'Kursi kantor Ergonomis',
                'ruang_kaki'           => 'Terdapat ruang untuk kaki',
                'tangan_kursi_baik'    => 'Terdapat tangan kursi dan kondisi baik',
                'tinggi_adjustable'    => 'Tinggi kursi bisa diubah/disesuaikan',
                'ruang_kerja_cukup'    => 'Ruang kerja mencukupi bagi semua staff',
                'lemari_tertutup'      => 'Pintu lemari penyimpanan dokumen tertutup',
                'pencahayaan_cukup'    => 'Pencahayaan mencukupi',
                'ventilasi'            => 'Terdapat ventilasi udara',
                'suhu_mencukupi'       => 'Suhu ruangan mencukupi',
            ],
        ],
        'pembuangan_sampah' => [
            'label'        => 'Pembuangan Sampah/Limbah',
            'foto_required'=> false,
            'items'        => [
                'kemasan_tersedia'   => 'Kemasan pembuangan tersedia sesuai standar',
                'sampah_domestik'    => 'Sampah Domestik tersedia',
                'sampah_tajam'       => 'Sampah Benda Tajam tersedia',
                'sampah_medis'       => 'Sampah Medis tersedia',
                'dibuang_sesuai'     => 'Sampah/limbah dibuang sesuai tempatnya',
                'kemasan_warna'      => 'Kemasan menggunakan warna standar',
                'ada_label'          => 'Terdapat label pada kemasan sampah/Limbah',
                'dibuang_teratur'    => 'Sampah/limbah dibuang secara teratur/kemasan tidak penuh',
                'ada_troli'          => 'Terdapat troli/alat pengangkut sampah/limbah',
                'troli_baik'         => 'Troli/alat pengangkut dalam kondisi baik dan bersih',
                'ada_tps'            => 'Terdapat tempat penyimpanan sampah/limbah sementara',
                'tps_bersih'         => 'Tempat penyimpanan sementara dalam kondisi bersih dan rapi',
                'tps_tertutup'       => 'Tempat penyimpanan limbah sementara tertutup',
                'kemasan_berlabel'   => 'Semua kemasan limbah diberi label yang jelas',
            ],
        ],
        'tabung_oksigen' => [
            'label'        => 'Tabung Oksigen Medis',
            'foto_required'=> false,
            'items'        => [
                'ada_troli'           => 'Tersedia troli pengangkut tabung',
                'ada_pengikat'        => 'Terdapat pengikat/penstabil tabung oksigen',
                'tabung_kosong_pisah' => 'Tabung kosong dipisahkan dan diberi label',
                'tanda_peringatan'    => 'Tanda peringatan Gas bertekanan tersedia',
                'indicator_isi'       => 'Terdapat indikator isi tabung',
                'penutup_kepala'      => 'Terdapat penutup kepala tabung',
            ],
        ],
        'bahan_berbahaya' => [
            'label'        => 'Bahan Berbahaya (B3)',
            'foto_required'=> false,
            'items'        => [
                'ada_sds'        => 'Tersedia SDS/LDKB bagi setiap bahan kimia',
                'sds_mudah'      => 'SDS/LDKB mudah didapat',
                'ada_apd'        => 'Tersedia Alat Pelindung Diri yang tepat',
                'kimia_lemari'   => 'Bahan kimia mudah terbakar disimpan di lemari tahan api',
                'ventilasi_cukup'=> 'Ruang penyimpanan bahan kimia memiliki ventilasi yang memadai',
            ],
        ],
        'bahaya_listrik' => [
            'label'        => 'Bahaya Listrik',
            'foto_required'=> false,
            'items'        => [
                'stop_kontak_baik'  => 'Stop kontak dalam keadaan baik',
                'kabel_baik'        => 'Kabel-kabel dalam keadaan baik',
                'ada_tray_kabel'    => 'Terdapat tray untuk kabel',
                'alat_berlabel'     => 'Alat-alat dalam perbaikan diberi label/dikunci',
            ],
        ],
        'toilet' => [
            'label'        => 'Toilet Umum/Staff',
            'foto_required'=> false,
            'items'        => [
                'bersih_tidak_bau' => 'Ruang toilet bersih dan tidak berbau',
                'ada_sabun'        => 'Tersedia sabun pencuci tangan',
                'ada_tissue'       => 'Tersedia tissue',
                'closet_baik'      => 'Urinase/closet bersih dan berfungsi baik',
            ],
        ],
        'papan_codered' => [
            'label'        => 'Papan Jadwal Petugas CodeRed & Disaster',
            'foto_required'=> true,
            'items'        => [
                'papan_diisi'   => 'Papan jadwal petugas Codered & Disaster diisi setiap hari',
                'helm_lengkap'  => 'Helm perlengkapan petugas Codered & Disaster lengkap',
                'kondisi_bersih'=> 'Kondisi bersih tidak berdebu',
            ],
        ],
    ];

    const PINTU_AKSES = [
        'pintu_samping_rm'      => 'Pintu Samping RM',
        'pintu_belakang_poli_tb'=> 'Pintu Belakang Poli TB',
        'pintu_belakang_igd'    => 'Pintu Belakang arah IGD (kecuali Akses Operasi OK)',
        'pintu_kmj'             => 'Pintu KMJ',
        'pintu_laundry'         => 'Pintu Laundry',
        'pintu_gizi'            => 'Pintu Gizi',
    ];

    // ── Relasi ───────────────────────────────────────────────────────

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PatrolInspeksiFoto::class, 'patrol_inspeksi_id');
    }

    public function fotosBySeksi(string $seksi)
    {
        return $this->fotos->where('seksi', $seksi);
    }

    // ── Accessor ─────────────────────────────────────────────────────

    public function getAreaLabelAttribute(): string
    {
        return self::AREA[$this->area] ?? $this->area;
    }

    /**
     * Hitung persentase kepatuhan (item YA / total item).
     */
    public function getPersentaseAttribute(): int
    {
        $total = 0;
        $ya    = 0;
        foreach (array_keys(self::SEKSI) as $seksi) {
            $data = $this->$seksi ?? [];
            foreach ($data as $val) {
                $total++;
                if ($val) $ya++;
            }
        }
        return $total > 0 ? (int) round($ya / $total * 100) : 0;
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
