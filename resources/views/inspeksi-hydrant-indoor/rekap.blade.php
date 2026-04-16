@extends('layouts.app')

@section('title', 'Rekap Pemeriksaan Hydrant Indoor')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Pemeriksaan Hydrant Indoor</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('inspeksi-hydrant-indoor.rekap') }}" class="row g-2 align-items-end">
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
                @include('exports.partials.buttons', ['route' => 'export.inspeksi-hydrant-indoor', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]])
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
                        <div class="text-muted small">Laporan Semua Aman</div>
                        @php
                            $semuaAman = $logbooks->getCollection()->filter(fn($l) => $l->hydrant_aman == 2)->count();
                        @endphp
                        <div class="h2 mb-0 text-success">{{ $semuaAman }}</div>
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
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Pemeriksaan Hydrant Indoor</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} laporan ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-flame-off" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada laporan pemeriksaan hydrant indoor untuk periode ini.</p>
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
                            <th class="text-center">Hydrant 1</th>
                            <th class="text-center">Hydrant 2</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logbooks as $log)
                        @php
                            $h1Ok = $log->hydrant_1 && $log->isHydrantAman($log->hydrant_1);
                            $h2Ok = $log->hydrant_2 && $log->isHydrantAman($log->hydrant_2);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ $log->tanggal->translatedFormat('l') }}</small>
                            </td>
                            <td>{{ $log->pjlp->nama ?? '-' }}</td>
                            <td><span class="badge bg-purple-lt text-purple">{{ $log->shift->nama ?? '-' }}</span></td>
                            <td class="small">{{ \App\Models\InspeksiHydrantIndoor::LOKASI[$log->lokasi] ?? $log->lokasi }}</td>
                            <td class="text-center">
                                @if(!$log->hydrant_1)
                                <span class="text-muted">—</span>
                                @elseif($h1Ok)
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check"></i></span>
                                @else
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-alert-triangle"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!$log->hydrant_2)
                                <span class="text-muted">—</span>
                                @elseif($h2Ok)
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check"></i></span>
                                @else
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-alert-triangle"></i></span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inspeksi-hydrant-indoor.show', $log) }}"
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
