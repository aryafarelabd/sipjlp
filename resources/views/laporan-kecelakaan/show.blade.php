@extends('layouts.app')

@section('title', 'Detail Laporan Kecelakaan')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('laporan-kecelakaan.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">K3</div>
                <h2 class="page-title">Detail Laporan Kecelakaan Kerja</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Header badge --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="text-muted small">Pelapor</div>
                        <div class="fw-medium">{{ $laporanKecelakaan->nama_pelapor }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Unit / Bagian</div>
                        <div class="fw-medium">{{ $laporanKecelakaan->unit_bagian }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tanggal &amp; Waktu</div>
                        <div class="fw-medium">{{ $laporanKecelakaan->tanggal->translatedFormat('d F Y') }} · {{ \Carbon\Carbon::parse($laporanKecelakaan->waktu)->format('H:i') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tipe</div>
                        <span class="badge bg-{{ $laporanKecelakaan->tipe_color }} fs-6">
                            {{ $laporanKecelakaan->tipe_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">

                {{-- Informasi Kecelakaan --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-map-pin me-1"></i>Informasi Kecelakaan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Tempat Kejadian</div>
                            <div class="fw-medium">{{ $laporanKecelakaan->tempat }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Saksi-Saksi</div>
                            <div class="small">{{ $laporanKecelakaan->saksi }}</div>
                        </div>
                    </div>
                </div>

                {{-- Data Korban --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-users me-1"></i>Data Korban</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="card card-sm bg-blue-lt">
                                    <div class="card-body text-center py-2">
                                        <div class="text-muted small">Laki-laki</div>
                                        <div class="h3 mb-0">{{ $laporanKecelakaan->jumlah_laki }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card card-sm bg-pink-lt">
                                    <div class="card-body text-center py-2">
                                        <div class="text-muted small">Perempuan</div>
                                        <div class="h3 mb-0">{{ $laporanKecelakaan->jumlah_perempuan }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Nama Korban</div>
                            <div class="small">{{ $laporanKecelakaan->nama_korban }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Umur Korban</div>
                            <div class="fw-medium">{{ $laporanKecelakaan->umur_korban }}</div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="text-muted small">Mati</div>
                                <div class="h4 mb-0 text-danger">{{ $laporanKecelakaan->akibat_mati }}</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="text-muted small">Luka Berat</div>
                                <div class="h4 mb-0 text-warning">{{ $laporanKecelakaan->akibat_luka_berat }}</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="text-muted small">Luka Ringan</div>
                                <div class="h4 mb-0 text-info">{{ $laporanKecelakaan->akibat_luka_ringan }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-muted small">Keterangan Cedera</div>
                            <div class="small">{{ $laporanKecelakaan->keterangan_cedera }}</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-6">

                {{-- Fakta --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-search me-1"></i>Fakta yang Didapat</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Kondisi yang Berbahaya</div>
                            <div class="small">{{ $laporanKecelakaan->kondisi_berbahaya }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Tindakan yang Berbahaya</div>
                            <div class="small">{{ $laporanKecelakaan->tindakan_berbahaya }}</div>
                        </div>
                    </div>
                </div>

                {{-- Uraian --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-file-text me-1"></i>Uraian Kejadian</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Kronologi Kejadian</div>
                            <div class="small">{{ $laporanKecelakaan->uraian_kejadian }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Sumber Kejadian</div>
                            <div class="small">{{ $laporanKecelakaan->sumber_kejadian }}</div>
                        </div>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-paperclip me-1"></i>Dokumen Pendukung</h3>
                    </div>
                    <div class="card-body">
                        @if($laporanKecelakaan->foto_bukti)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Foto Bukti</div>
                            <a href="{{ asset('storage/' . $laporanKecelakaan->foto_bukti) }}"
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-external-link me-1"></i>Buka Foto Bukti
                            </a>
                        </div>
                        @endif
                        @if($laporanKecelakaan->file_formulir)
                        <div>
                            <div class="text-muted small mb-1">File Formulir Hard Copy</div>
                            <a href="{{ asset('storage/' . $laporanKecelakaan->file_formulir) }}"
                               target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="ti ti-download me-1"></i>Unduh Formulir
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
