@extends('layouts.app')

@section('title', 'Rekap Lembar Kerja CS')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Rekap Lembar Kerja CS</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-sm-auto">
                        <label class="form-label mb-1 small">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label mb-1 small">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm">
                        <label class="form-label mb-1 small">Cari Petugas</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama petugas..." value="{{ $search }}">
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label mb-1 small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Menunggu</option>
                            <option value="validated" {{ $status == 'validated' ? 'selected' : '' }}>Divalidasi</option>
                            <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-search me-1"></i>Filter
                        </button>
                    </div>
                    <div class="col-sm-auto ms-auto">
                        @include('exports.partials.buttons', [
                            'route'  => 'export.lembar-kerja-cs',
                            'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search, 'status' => $status],
                        ])
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-blue text-white avatar"><i class="ti ti-file-text"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $totalEntri }}</div>
                                <div class="text-muted small">Total Entri</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-yellow text-white avatar"><i class="ti ti-clock"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $pending }}</div>
                                <div class="text-muted small">Menunggu Validasi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Tanggal</th>
                            <th>Area</th>
                            <th>Shift</th>
                            <th>Periodik</th>
                            <th>Extra</th>
                            <th>Status</th>
                            <th style="width:80px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lembarKerja as $lk)
                        <tr>
                            <td>{{ $lk->pjlp->nama ?? '-' }}</td>
                            <td>{{ $lk->tanggal->translatedFormat('d M Y') }}</td>
                            <td>{{ $lk->area->nama ?? '-' }}</td>
                            <td>{{ $lk->shift->nama ?? '-' }}</td>
                            <td class="text-center">{{ count($lk->kegiatan_periodik ?? []) }}</td>
                            <td class="text-center">{{ count($lk->kegiatan_extra_job ?? []) }}</td>
                            <td>
                                <span class="badge bg-{{ $lk->status_color }}-lt text-{{ $lk->status_color }}">
                                    {{ $lk->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('lembar-kerja-cs.show', $lk) }}"
                                   class="btn btn-sm btn-ghost-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-clipboard-off me-1"></i>Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lembarKerja->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $lembarKerja->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
