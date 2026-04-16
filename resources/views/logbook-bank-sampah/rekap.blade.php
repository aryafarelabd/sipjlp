@extends('layouts.app')

@section('title', 'Rekap Logbook Bank Sampah')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Logbook Bank Sampah</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('logbook-bank-sampah.rekap') }}" class="row g-2 align-items-end">
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
                @include('exports.partials.buttons', ['route' => 'export.logbook-bank-sampah', 'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]])
            </div>
            </div>
        </div>

        {{-- Statistik per jenis --}}
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-chart-bar me-1"></i>Total Timbulan per Jenis Sampah Bulan Ini</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $totalEntri }} pengambilan</span>
                </div>
            </div>
            <div class="card-body">
                @php
                    $grandTotal = 0;
                    foreach(array_keys($jenis) as $f) $grandTotal += $stats->{"total_{$f}"} ?? 0;
                @endphp
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-green fs-4">{{ number_format($grandTotal, 1) }} kg</span>
                            <span class="text-muted small">total semua jenis sampah bulan ini</span>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    @foreach($jenis as $field => $label)
                    @php $val = $stats->{"total_{$field}"} ?? 0; @endphp
                    <div class="col-sm-6 col-lg-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">{{ $label }}</span>
                                    <span class="small fw-medium text-teal">{{ number_format($val, 1) }} kg</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-teal"
                                         style="width: {{ $grandTotal > 0 ? round($val / $grandTotal * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Logbook Bank Sampah</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} entri ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-recycle-off" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                    <p class="empty-subtitle text-muted">Belum ada logbook bank sampah untuk periode ini.</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Jenis & Berat Sampah</th>
                            <th class="text-center">Total</th>
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
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($jenis as $field => $label)
                                        @if($log->$field > 0)
                                        <span class="badge bg-teal-lt text-teal">
                                            {{ $label }}: {{ number_format($log->$field, 1) }} kg
                                        </span>
                                        @endif
                                    @endforeach
                                </div>
                                @if($log->catatan)
                                <small class="text-muted d-block mt-1"><i class="ti ti-note me-1"></i>{{ $log->catatan }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-medium text-green">{{ number_format($log->total_berat, 1) }} kg</span>
                            </td>
                            <td>
                                @if($log->fotos->isNotEmpty())
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-primary"
                                        title="Foto ({{ $log->fotos->count() }})"
                                        onclick="lihatFotos({{ $log->fotos->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Bukti Dokumentasi')">
                                    <i class="ti ti-photo"></i>
                                </button>
                                @endif
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
