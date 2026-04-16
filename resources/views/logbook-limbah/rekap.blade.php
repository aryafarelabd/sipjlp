@extends('layouts.app')

@section('title', 'Rekap Logbook Limbah')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Logbook Limbah</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('logbook-limbah.rekap') }}" class="row g-2 align-items-end">
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
                        <label class="form-label mb-1 small">Area</label>
                        <select name="area_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>
                                {{ $area->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
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
                @include('exports.partials.buttons', ['route' => 'export.logbook-limbah', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'area_id' => $areaId, 'search' => $search]])
            </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar"><i class="ti ti-list-numbers"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Total Entri</div>
                                <div class="text-muted small">Bulan ini</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0">{{ $stats->total_entri ?? 0 }}</span>
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
                                <span class="bg-blue text-white avatar"><i class="ti ti-trash"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Total Limbah Domestik</div>
                                <div class="text-muted small">Bulan ini</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 text-blue">{{ number_format($stats->total_domestik ?? 0, 1) }}</span>
                                <small class="text-muted"> kg</small>
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
                                <span class="bg-green text-white avatar"><i class="ti ti-leaf"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Total Kompos</div>
                                <div class="text-muted small">Bulan ini</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 text-green">{{ number_format($stats->total_kompos ?? 0, 1) }}</span>
                                <small class="text-muted"> kg</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Logbook</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} entri ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-clipboard-off" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada logbook yang diisi untuk periode ini.</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Area</th>
                            <th>Shift</th>
                            <th class="text-end">Domestik (kg)</th>
                            <th class="text-end">Kompos (kg)</th>
                            <th>Foto APD</th>
                            <th>Foto Timbangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logbooks as $log)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ $log->tanggal->translatedFormat('l') }}</small>
                            </td>
                            <td>{{ $log->pjlp->nama ?? '-' }}</td>
                            <td>{{ $log->area->nama ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary-lt text-secondary">
                                    {{ $log->shift->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="text-end fw-medium text-blue">
                                {{ number_format($log->berat_domestik, 1) }}
                            </td>
                            <td class="text-end fw-medium text-green">
                                {{ number_format($log->berat_kompos, 1) }}
                            </td>
                            <td>
                                @if($log->fotosApd->isNotEmpty())
                                <button type="button"
                                        class="btn btn-sm btn-ghost-primary"
                                        onclick="lihatFotos({{ $log->fotosApd->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto APD — {{ $log->pjlp->nama ?? '' }}')">
                                    <i class="ti ti-shield-check me-1"></i>{{ $log->fotosApd->count() }} foto
                                </button>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->fotosTimbangan->isNotEmpty())
                                <button type="button"
                                        class="btn btn-sm btn-ghost-success"
                                        onclick="lihatFotos({{ $log->fotosTimbangan->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto Timbangan — {{ $log->pjlp->nama ?? '' }}')">
                                    <i class="ti ti-scale me-1"></i>{{ $log->fotosTimbangan->count() }} foto
                                </button>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($log->catatan)
                        <tr>
                            <td colspan="8" class="pt-0">
                                <small class="text-muted"><i class="ti ti-note me-1"></i>{{ $log->catatan }}</small>
                            </td>
                        </tr>
                        @endif
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

{{-- Modal foto --}}
<div class="modal modal-blur fade" id="fotoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoModalTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2" id="fotoModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function lihatFotos(urls, judul) {
    document.getElementById('fotoModalTitle').textContent = judul;
    const body = document.getElementById('fotoModalBody');
    body.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'd-flex flex-wrap gap-2 justify-content-center';
    urls.forEach(function (url) {
        const a = document.createElement('a');
        a.href = url; a.target = '_blank';
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'max-height:300px;max-width:100%;object-fit:contain;border-radius:6px;cursor:pointer;';
        a.appendChild(img);
        wrap.appendChild(a);
    });
    body.appendChild(wrap);
    new bootstrap.Modal(document.getElementById('fotoModal')).show();
}
</script>
@endpush
