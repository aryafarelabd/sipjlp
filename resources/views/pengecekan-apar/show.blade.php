@extends('layouts.app')

@section('title', 'Detail Pengecekan APAR & APAB')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('pengecekan-apar.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Detail Pengecekan APAR &amp; APAB</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Header --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="text-muted small">Petugas</div>
                        <div class="fw-medium">{{ $pengecekanApar->pjlp->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tanggal</div>
                        <div class="fw-medium">{{ $pengecekanApar->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Shift</div>
                        <div class="fw-medium">{{ $pengecekanApar->shift->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Lokasi</div>
                        <div class="fw-medium">{{ $lokasi[$pengecekanApar->lokasi] ?? $pengecekanApar->lokasi }}</div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    @php $buruk = $pengecekanApar->jumlah_buruk; @endphp
                    <span class="badge fs-6 {{ $buruk == 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ $buruk == 0 ? 'Semua Unit Baik' : $buruk . ' Unit Kondisi Buruk' }}
                    </span>
                    @if($pengecekanApar->rincian_aman)
                    <span class="badge fs-6 bg-success">Rincian Semua Aman</span>
                    @else
                    <span class="badge fs-6 bg-warning text-dark">Ada Rincian Tidak Aman</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Unit Checklist --}}
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-fire-extinguisher me-2"></i>Status Unit APAR &amp; APAB
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <thead>
                        <tr>
                            <th>Nomor &amp; Nama Ruangan</th>
                            <th width="120" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitDefs as $unitKey => $unitLabel)
                        @php $val = $pengecekanApar->units[$unitKey] ?? null; @endphp
                        <tr>
                            <td class="small fw-medium">{{ $unitLabel }}</td>
                            <td class="text-center">
                                @if($val === 'baik')
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Baik</span>
                                @elseif($val === 'buruk')
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-alert-triangle me-1"></i>Buruk</span>
                                @else
                                <span class="badge bg-secondary-lt text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pengecekanApar->keterangan_buruk)
            <div class="card-footer text-muted small">
                <i class="ti ti-notes me-1"></i><strong>Keterangan buruk:</strong> {{ $pengecekanApar->keterangan_buruk }}
            </div>
            @endif
        </div>

        {{-- Rincian Pemeriksaan --}}
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-clipboard-list me-2"></i>Rincian Pemeriksaan
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="text-muted small">Berat</div>
                        <div class="fw-medium">{{ $pengecekanApar->berat ?: '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small">Tekanan</div>
                        <div class="fw-medium">{{ $pengecekanApar->tekanan ?: '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small">Masa Berlaku</div>
                        <div class="fw-medium">{{ $pengecekanApar->masa_berlaku->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th width="160" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="small fw-medium">Kondisi APAR &amp; APAB</td>
                                <td class="text-center">
                                    <span class="badge {{ $pengecekanApar->kondisi === 'baik' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                        {{ $pengecekanApar->kondisi === 'baik' ? 'Baik' : 'Buruk' }}
                                    </span>
                                    @if($pengecekanApar->kondisi_ket)
                                    <div class="small text-muted mt-1">{{ $pengecekanApar->kondisi_ket }}</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="small fw-medium">PIN Segel</td>
                                <td class="text-center">
                                    <span class="badge {{ $pengecekanApar->pin_segel === 'ada' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                        {{ $pengecekanApar->pin_segel === 'ada' ? 'Ada' : 'Tidak' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="small fw-medium">Handle</td>
                                <td class="text-center">
                                    <span class="badge {{ $pengecekanApar->handle === 'baik' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                        {{ $pengecekanApar->handle === 'baik' ? 'Baik / Berfungsi' : 'Buruk / Tidak Berfungsi' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="small fw-medium">Petunjuk Penggunaan</td>
                                <td class="text-center">
                                    <span class="badge {{ $pengecekanApar->petunjuk === 'ada' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                        {{ $pengecekanApar->petunjuk === 'ada' ? 'Ada' : 'Tidak' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="small fw-medium">Tanda Segitiga Api</td>
                                <td class="text-center">
                                    <span class="badge {{ $pengecekanApar->segitiga_api === 'ada' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                        {{ $pengecekanApar->segitiga_api === 'ada' ? 'Ada' : 'Tidak' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($pengecekanApar->keterangan_lain)
                <div class="mt-2 text-muted small">
                    <i class="ti ti-notes me-1"></i><strong>Keterangan lain:</strong> {{ $pengecekanApar->keterangan_lain }}
                </div>
                @endif
            </div>
        </div>

        {{-- Foto Bukti --}}
        @if(!empty($pengecekanApar->foto_bukti))
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-camera me-2"></i>Foto Bukti Pemeriksaan</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    @foreach($pengecekanApar->foto_bukti as $index => $foto)
                    <a href="{{ Storage::url($foto) }}" target="_blank" title="Foto {{ $index + 1 }}">
                        <img src="{{ Storage::url($foto) }}"
                             alt="Foto bukti {{ $index + 1 }}"
                             style="width:160px;height:160px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
