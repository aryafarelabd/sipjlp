@extends('layouts.app')

@section('title', 'Rekap Laporan Kecelakaan Kerja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">K3</div>
                <h2 class="page-title">Rekap Laporan Kecelakaan, Insiden &amp; Ketidaksesuaian</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('laporan-kecelakaan.rekap') }}" class="row g-2 align-items-end">
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
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Tipe</label>
                        <select name="tipe_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            @foreach($tipe as $k => $l)
                            <option value="{{ $k }}" {{ $tipeFilter == $k ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label mb-1 small">Pelapor / Korban / Tempat</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stat per Tipe --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Total Laporan</div>
                        <div class="h2 mb-0">{{ $totalEntri }}</div>
                    </div>
                </div>
            </div>
            @foreach($tipe as $k => $l)
            @php $colors = ['accident' => 'danger', 'incident' => 'warning', 'near_miss' => 'info']; @endphp
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">{{ $l }}</div>
                        <div class="h2 mb-0 text-{{ $colors[$k] ?? 'secondary' }}">
                            {{ $statTipe[$k] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Data Laporan</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $logbooks->total() }} laporan ditemukan</span>
                </div>
            </div>
            @if($logbooks->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-shield-check" style="font-size:2.5rem; color:#2fb344;"></i></div>
                    <p class="empty-title">Tidak ada laporan</p>
                    <p class="empty-subtitle text-muted">Tidak ada insiden yang dilaporkan untuk periode ini.</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelapor</th>
                            <th>Unit</th>
                            <th>Tempat</th>
                            <th class="text-center">Korban</th>
                            <th class="text-center">Akibat</th>
                            <th>Tipe</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logbooks as $log)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->waktu)->format('H:i') }}</small>
                            </td>
                            <td>{{ $log->nama_pelapor }}</td>
                            <td class="small text-muted">{{ $log->unit_bagian }}</td>
                            <td class="small">{{ $log->tempat }}</td>
                            <td class="text-center">
                                <span class="badge bg-blue-lt text-blue">L: {{ $log->jumlah_laki }}</span>
                                <span class="badge bg-pink-lt text-pink">P: {{ $log->jumlah_perempuan }}</span>
                            </td>
                            <td class="text-center" style="white-space:nowrap;">
                                @if($log->akibat_mati > 0)
                                <span class="badge bg-danger-lt text-danger me-1">Mati: {{ $log->akibat_mati }}</span>
                                @endif
                                @if($log->akibat_luka_berat > 0)
                                <span class="badge bg-warning-lt text-warning me-1">LB: {{ $log->akibat_luka_berat }}</span>
                                @endif
                                @if($log->akibat_luka_ringan > 0)
                                <span class="badge bg-info-lt text-info">LR: {{ $log->akibat_luka_ringan }}</span>
                                @endif
                                @if($log->akibat_mati == 0 && $log->akibat_luka_berat == 0 && $log->akibat_luka_ringan == 0)
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->tipe_color }}-lt text-{{ $log->tipe_color }}">
                                    {{ $log->tipe_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('laporan-kecelakaan.show', $log) }}"
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
