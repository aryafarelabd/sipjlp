@extends('layouts.app')

@section('title', 'Rekap Pengawasan Proyek')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Pengawasan Pembangunan &amp; Aktivitas Proyek</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('pengawasan-proyek.rekap') }}" class="row g-2 align-items-end">
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label mb-1 small">Petugas / Nama Proyek</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            <div class="card-footer d-flex justify-content-end py-2 border-top">
                @include('exports.partials.buttons', ['route' => 'export.pengawasan-proyek', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]])
            </div>
            </div>
        </div>

        {{-- Stat --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Total Laporan</div>
                        <div class="h2 mb-0">{{ $totalEntri }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Rata-rata Kepatuhan</div>
                        @php
                            $rataPatuh = $logbooks->count() > 0
                                ? round($logbooks->getCollection()->avg(fn($l) => $l->persentase))
                                : 0;
                        @endphp
                        <div class="h2 mb-0 {{ $rataPatuh >= 80 ? 'text-success' : ($rataPatuh >= 50 ? 'text-warning' : 'text-danger') }}">
                            {{ $rataPatuh }}%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Periode</div>
                        <div class="fw-medium">
                            {{ \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') }} {{ $tahun }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Pengawasan Proyek</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} laporan ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-building-factory-2" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada laporan pengawasan proyek untuk periode ini.</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Shift</th>
                            <th>Nama Proyek</th>
                            <th>Lokasi</th>
                            <th>Kepatuhan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logbooks as $log)
                        @php $pct = $log->persentase; @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ $log->tanggal->translatedFormat('l') }}</small>
                            </td>
                            <td>{{ $log->pjlp->nama ?? '-' }}</td>
                            <td><span class="badge bg-purple-lt text-purple">{{ $log->shift->nama ?? '-' }}</span></td>
                            <td class="small">{{ $log->nama_proyek }}</td>
                            <td class="small text-muted">{{ $log->lokasi }}</td>
                            <td style="min-width:140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="small fw-medium {{ $pct >= 80 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ $pct }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('pengawasan-proyek.show', $log) }}"
                                   class="btn btn-sm btn-ghost-primary">
                                    <i class="ti ti-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                {{ $logbooks->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
