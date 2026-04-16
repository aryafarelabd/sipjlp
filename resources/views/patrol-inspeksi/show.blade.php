@extends('layouts.app')

@section('title', 'Detail Laporan Security Patrol')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('patrol-inspeksi.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Security Patrol</div>
                <h2 class="page-title">Detail Laporan Inspeksi</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Header Info --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="text-muted small">Petugas</div>
                        <div class="fw-medium">{{ $patrolInspeksi->pjlp->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tanggal</div>
                        <div class="fw-medium">{{ $patrolInspeksi->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Shift</div>
                        <div class="fw-medium">{{ $patrolInspeksi->shift->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Area Inspeksi</div>
                        <div class="fw-medium">{{ $patrolInspeksi->area_label }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Kepatuhan Keseluruhan</span>
                        <span class="fw-bold {{ $patrolInspeksi->persentase >= 80 ? 'text-success' : ($patrolInspeksi->persentase >= 60 ? 'text-warning' : 'text-danger') }}">
                            {{ $patrolInspeksi->persentase }}%
                        </span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar {{ $patrolInspeksi->persentase >= 80 ? 'bg-success' : ($patrolInspeksi->persentase >= 60 ? 'bg-warning' : 'bg-danger') }}"
                             style="width:{{ $patrolInspeksi->persentase }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seksi Inspeksi --}}
        @foreach($seksi as $key => $config)
        @php
            $data  = $patrolInspeksi->$key ?? [];
            $fotos = $patrolInspeksi->fotosBySeksi($key);
            $totalYa   = collect($data)->filter()->count();
            $totalItem = count($config['items']);
        @endphp
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-clipboard-check me-2"></i>{{ $config['label'] }}
                </h3>
                <div class="card-actions">
                    <span class="badge bg-white text-dark">{{ $totalYa }}/{{ $totalItem }} OK</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <tbody>
                        @foreach($config['items'] as $itemKey => $itemLabel)
                        @php $val = $data[$itemKey] ?? null; @endphp
                        <tr>
                            <td class="small">{{ $itemLabel }}</td>
                            <td width="80" class="text-center">
                                @if($val === true || $val === 1)
                                    <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Ya</span>
                                @elseif($val === false || $val === 0)
                                    <span class="badge bg-danger-lt text-danger"><i class="ti ti-x me-1"></i>Tidak</span>
                                @else
                                    <span class="badge bg-secondary-lt text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($fotos->isNotEmpty())
            <div class="card-footer py-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($fotos as $foto)
                    <a href="{{ asset('storage/'.$foto->path) }}" target="_blank">
                        <img src="{{ asset('storage/'.$foto->path) }}"
                             style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach

        {{-- Pintu Akses Malam --}}
        @if($patrolInspeksi->pintu_akses)
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-lock me-2"></i>Pembatasan Akses Malam (21.00 WIB)
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <tbody>
                        @foreach($pintuAkses as $pKey => $pLabel)
                        @php $val = $patrolInspeksi->pintu_akses[$pKey] ?? null; @endphp
                        <tr>
                            <td class="small">{{ $pLabel }}</td>
                            <td width="100" class="text-center">
                                @if($val === true || $val === 1)
                                    <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Sudah</span>
                                @elseif($val === false || $val === 0)
                                    <span class="badge bg-danger-lt text-danger"><i class="ti ti-x me-1"></i>Belum</span>
                                @else
                                    <span class="badge bg-secondary-lt text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($patrolInspeksi->alasan_pintu)
            <div class="card-footer py-2">
                <small class="text-muted"><i class="ti ti-note me-1"></i>{{ $patrolInspeksi->alasan_pintu }}</small>
            </div>
            @endif
            @php $fotoTemuan = $patrolInspeksi->fotosBySeksi('temuan'); @endphp
            @if($fotoTemuan->isNotEmpty())
            <div class="card-footer py-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($fotoTemuan as $foto)
                    <a href="{{ asset('storage/'.$foto->path) }}" target="_blank">
                        <img src="{{ asset('storage/'.$foto->path) }}"
                             style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Rekomendasi --}}
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-bulb me-2"></i>Rekomendasi Perbaikan
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;">{{ $patrolInspeksi->rekomendasi }}</p>
            </div>
        </div>

    </div>
</div>
@endsection
