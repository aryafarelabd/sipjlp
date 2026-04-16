@extends('layouts.app')

@section('title', 'Rekap Pengecekan APAR & APAB')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Pengecekan APAR &amp; APAB</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('pengecekan-apar.rekap') }}" class="row g-2 align-items-end">
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
                    <div class="col-sm-3">
                        <label class="form-label mb-1 small">Lokasi</label>
                        <select name="lokasi_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            @foreach($lokasi as $k => $l)
                            <option value="{{ $k }}" {{ $lokasiFilter == $k ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label mb-1 small">Nama Petugas</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari nama..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            <div class="card-footer d-flex justify-content-end py-2 border-top">
                @include('exports.partials.buttons', ['route' => 'export.pengecekan-apar', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'lokasi_filter' => $lokasiFilter, 'search' => $search]])
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
                        <div class="text-muted small">Laporan Semua Baik</div>
                        @php
                            $semuaBaik = $logbooks->getCollection()->filter(fn($l) => $l->jumlah_buruk == 0)->count();
                        @endphp
                        <div class="h2 mb-0 text-success">{{ $semuaBaik }}</div>
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
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Pengecekan APAR &amp; APAB</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} laporan ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-fire-extinguisher" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada laporan pengecekan APAR untuk periode ini.</p>
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
                            <th>Lokasi</th>
                            <th class="text-center">Unit Buruk</th>
                            <th class="text-center">Kondisi</th>
                            <th>Masa Berlaku</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logbooks as $log)
                        @php
                            $buruk       = $log->jumlah_buruk;
                            $totalUnit   = $log->total_unit;
                            $rincianAman = $log->rincian_aman;
                            $expired     = $log->masa_berlaku->isPast();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ $log->tanggal->translatedFormat('l') }}</small>
                            </td>
                            <td>{{ $log->pjlp->nama ?? '-' }}</td>
                            <td><span class="badge bg-purple-lt text-purple">{{ $log->shift->nama ?? '-' }}</span></td>
                            <td class="small">{{ \App\Models\PengecekanApar::LOKASI[$log->lokasi] ?? $log->lokasi }}</td>
                            <td class="text-center">
                                @if($buruk == 0)
                                <span class="badge bg-success-lt text-success">Semua Baik ({{ $totalUnit }})</span>
                                @else
                                <span class="badge bg-danger-lt text-danger">{{ $buruk }}/{{ $totalUnit }} Buruk</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rincianAman)
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check"></i></span>
                                @else
                                <span class="badge bg-warning-lt text-warning"><i class="ti ti-alert-triangle"></i></span>
                                @endif
                            </td>
                            <td>
                                <span class="{{ $expired ? 'text-danger fw-medium' : 'text-muted' }} small">
                                    {{ $log->masa_berlaku->translatedFormat('d M Y') }}
                                    @if($expired)<i class="ti ti-alert-circle ms-1"></i>@endif
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('pengecekan-apar.show', $log) }}"
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
