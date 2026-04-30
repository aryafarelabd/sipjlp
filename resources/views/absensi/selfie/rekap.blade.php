@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Rekap Absensi</h2>
                <div class="text-muted">
                    {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.rekap') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m == $bulan)>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Nama PJLP</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('absensi.rekap') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekapan Per PJLP — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</h3>
            <div class="card-options">
                <span class="text-muted small me-3">{{ $summaryList->count() }} PJLP</span>
                <a href="{{ route('absensi.rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                   class="btn btn-sm btn-success">
                    <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>Nama PJLP</th>
                        <th>NIP</th>
                        <th>Unit</th>
                        <th class="text-center">Hari Kerja</th>
                        <th class="text-center">Alpha<br><small class="text-muted fw-normal">hari</small></th>
                        <th class="text-center">Izin/Cuti<br><small class="text-muted fw-normal">hari</small></th>
                        <th class="text-center">Telat<br><small class="text-muted fw-normal">menit</small></th>
                        <th class="text-center">Pulang Cepat<br><small class="text-muted fw-normal">menit</small></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryList as $row)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $row['pjlp']->nama }}</div>
                            </td>
                            <td class="text-muted small">{{ $row['pjlp']->nip ?? '-' }}</td>
                            <td>
                                <span class="badge bg-blue-lt">{{ ucfirst($row['pjlp']->unit->value) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $row['hari_kerja'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($row['total_alpha'] > 0)
                                    <span class="badge bg-danger">{{ $row['total_alpha'] }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['total_izin'] > 0)
                                    <span class="badge bg-info">{{ $row['total_izin'] }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['total_telat_menit'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $row['total_telat_menit'] }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['total_pulang_cepat'] > 0)
                                    <span class="badge bg-orange">{{ $row['total_pulang_cepat'] }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('absensi.rekap', ['pjlp_id' => $row['pjlp']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-list-details me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data PJLP.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
