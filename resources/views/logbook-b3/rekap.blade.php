@extends('layouts.app')

@section('title', 'Rekap Logbook B3')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Logbook Limbah B3</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('logbook-b3.rekap') }}" class="row g-2 align-items-end">
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
                @include('exports.partials.buttons', ['route' => 'export.logbook-b3', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'area_id' => $areaId, 'search' => $search]])
            </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Total Entri</div>
                        <div class="h2 mb-0">{{ $stats->total_entri ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Plastik Kuning</div>
                        <div class="h2 mb-0 text-warning">{{ number_format($stats->total_pk ?? 0, 1) }}</div>
                        <div class="text-muted" style="font-size:11px;">kg</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Safety Box</div>
                        <div class="h2 mb-0 text-orange">{{ number_format($stats->total_safety_box ?? 0, 1) }}</div>
                        <div class="text-muted" style="font-size:11px;">kg</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Limbah Cair</div>
                        <div class="h2 mb-0 text-blue">{{ number_format($stats->total_cair ?? 0, 1) }}</div>
                        <div class="text-muted" style="font-size:11px;">kg</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Hepafilter</div>
                        <div class="h2 mb-0 text-purple">{{ number_format($stats->total_hepafilter ?? 0, 1) }}</div>
                        <div class="text-muted" style="font-size:11px;">kg</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Non Infeksius</div>
                        <div class="h2 mb-0 text-secondary">{{ number_format($stats->total_non_infeksius ?? 0, 1) }}</div>
                        <div class="text-muted" style="font-size:11px;">kg</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Logbook B3</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} entri ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-clipboard-off" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada logbook B3 yang diisi untuk periode ini.</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Area / Shift</th>
                            <th class="text-center">
                                <span class="badge bg-warning-lt text-warning">Plastik Kuning</span>
                            </th>
                            <th class="text-center">
                                <span class="badge bg-orange-lt text-orange">Safety Box</span>
                            </th>
                            <th class="text-center">
                                <span class="badge bg-blue-lt text-blue">Cair</span>
                            </th>
                            <th class="text-center">
                                <span class="badge bg-purple-lt text-purple">Hepa</span>
                            </th>
                            <th class="text-center">
                                <span class="badge bg-secondary-lt text-secondary">Non-Inf</span>
                            </th>
                            <th>Foto</th>
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
                            <td>
                                <div class="small">{{ $log->area->nama ?? '-' }}</div>
                                <span class="badge bg-secondary-lt text-secondary">{{ $log->shift->nama ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($log->total_pk > 0)
                                    <span class="fw-medium text-warning">{{ number_format($log->total_pk, 1) }} kg</span>
                                    <button type="button" class="btn btn-xs btn-ghost-secondary ms-1"
                                            onclick="lihatDetailPk({{ json_encode($log) }})"
                                            title="Detail per lantai"><i class="ti ti-list-details"></i></button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->safety_box_kg)
                                    <div class="fw-medium text-orange">{{ number_format($log->safety_box_kg, 1) }} kg</div>
                                    @if($log->safety_box_asal)
                                    <small class="text-muted">{{ $log->safety_box_asal }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->cair_kg)
                                    <div class="fw-medium text-blue">{{ number_format($log->cair_kg, 1) }} kg</div>
                                    @if($log->cair_asal)
                                    <small class="text-muted">{{ $log->cair_asal }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->hepafilter_kg)
                                    <div class="fw-medium text-purple">{{ number_format($log->hepafilter_kg, 1) }} kg</div>
                                    @if($log->hepafilter_asal)
                                    <small class="text-muted">{{ $log->hepafilter_asal }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->non_infeksius_kg)
                                    <div class="fw-medium text-secondary">{{ number_format($log->non_infeksius_kg, 1) }} kg</div>
                                    @if($log->non_infeksius_jenis)
                                    <small class="text-muted">{{ $log->non_infeksius_jenis }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($log->fotosApd->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-icon btn-ghost-primary"
                                            title="Foto APD ({{ $log->fotosApd->count() }})"
                                            onclick="lihatFotos({{ $log->fotosApd->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto APD')">
                                        <i class="ti ti-shield-check"></i>
                                    </button>
                                    @endif
                                    @if($log->fotosTimbangan->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-icon btn-ghost-success"
                                            title="Foto Timbangan ({{ $log->fotosTimbangan->count() }})"
                                            onclick="lihatFotos({{ $log->fotosTimbangan->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto Timbangan')">
                                        <i class="ti ti-scale"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($log->catatan)
                        <tr>
                            <td colspan="9" class="pt-0">
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

{{-- Modal detail plastik kuning --}}
<div class="modal modal-blur fade" id="pkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Plastik Kuning per Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pkModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const pkLokasi = {
    pk_lt1:      'Lantai 1',
    pk_igd:      'IGD',
    pk_lt2:      'Lantai 2',
    pk_ok:       'OK',
    pk_lt3:      'Lantai 3',
    pk_lt4:      'Lantai 4',
    pk_utilitas: 'Utilitas',
    pk_taman:    'Taman / Halaman',
};

function lihatDetailPk(log) {
    const body = document.getElementById('pkModalBody');
    let html = '<div class="divide-y">';
    let ada = false;
    for (const [key, label] of Object.entries(pkLokasi)) {
        if (log[key] && parseFloat(log[key]) > 0) {
            html += `<div class="row py-2"><div class="col">${label}</div>
                     <div class="col-auto fw-medium text-warning">${parseFloat(log[key]).toFixed(1)} kg</div></div>`;
            ada = true;
        }
    }
    if (!ada) html += '<p class="text-muted text-center py-3">Tidak ada data</p>';
    html += '</div>';
    body.innerHTML = html;
    new bootstrap.Modal(document.getElementById('pkModal')).show();
}

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
