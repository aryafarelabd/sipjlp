@extends('layouts.app')

@section('title', 'Detail Pengawasan Proyek')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('pengawasan-proyek.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Detail Pengawasan Proyek</h2>
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
                        <div class="fw-medium">{{ $pengawasanProyek->pjlp->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tanggal</div>
                        <div class="fw-medium">{{ $pengawasanProyek->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Shift</div>
                        <div class="fw-medium">{{ $pengawasanProyek->shift->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Nama Proyek</div>
                        <div class="fw-medium">{{ $pengawasanProyek->nama_proyek }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Lokasi</div>
                        <div class="fw-medium">{{ $pengawasanProyek->lokasi }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Tingkat Kepatuhan</div>
                        @php
                            $pct   = $pengawasanProyek->persentase;
                            $score = $pengawasanProyek->score;
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="fw-bold {{ $pct >= 80 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">
                                {{ $pct }}%
                            </span>
                            <span class="text-muted small">({{ $score['ya'] }}/{{ $score['total'] }} YA)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Per Seksi --}}
        @foreach($seksi as $seksiKey => $seksiConfig)
        @php $data = $pengawasanProyek->$seksiKey ?? []; @endphp
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-clipboard-check me-2"></i>{{ $seksiConfig['label'] }}
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <thead>
                        <tr>
                            <th>Pernyataan</th>
                            <th width="100" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($seksiConfig['items'] as $itemKey => $itemLabel)
                        @php $val = $data['items'][$itemKey] ?? null; @endphp
                        <tr>
                            <td class="small">{{ $itemLabel }}</td>
                            <td class="text-center">
                                @if($val === 'ya')
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>YA</span>
                                @elseif($val === 'tidak')
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-x me-1"></i>TIDAK</span>
                                @else
                                <span class="badge bg-secondary-lt text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(!empty($data['upaya_perbaikan']))
            <div class="card-footer">
                <div class="text-muted small mb-1">Upaya Perbaikan</div>
                <div class="small">{{ $data['upaya_perbaikan'] }}</div>
            </div>
            @endif
        </div>
        @endforeach

        {{-- Foto --}}
        @if($pengawasanProyek->foto)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-camera me-1"></i>Dokumentasi Foto</h3>
            </div>
            <div class="card-body text-center">
                <img src="{{ $pengawasanProyek->foto_url }}"
                     class="img-fluid rounded" style="max-height:400px;"
                     alt="Foto temuan">
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
