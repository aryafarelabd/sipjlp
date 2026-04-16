@extends('layouts.app')

@section('title', 'Rekap Gerakan Jumat Sehat')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">Rekap Gerakan Jumat Sehat</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('gerakan-jumat-sehat.rekap') }}" class="row g-2 align-items-end">
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
                    @if(auth()->user()->hasRole('admin') || (auth()->user()->unit?->value ?? '') === 'all')
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Unit</label>
                        <select name="unit_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit</option>
                            <option value="security" {{ $unitFilter === 'security' ? 'selected' : '' }}>Security</option>
                            <option value="cleaning" {{ $unitFilter === 'cleaning' ? 'selected' : '' }}>Cleaning Service</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-sm-4">
                        <label class="form-label mb-1 small">Nama Peserta</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari nama..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stat --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Total Kehadiran</div>
                        <div class="h2 mb-0">{{ $totalEntri }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small">Sudah ≥ 2x</div>
                        @php $sudahCukup = $pjlpList->filter(fn($p) => ($kehadiranRaw[$p->id] ?? collect())->count() >= 2)->count(); @endphp
                        <div class="h2 mb-0 text-success">{{ $sudahCukup }}</div>
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

        {{-- Tabel per PJLP --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-table me-1"></i>Kehadiran per Peserta</h3>
                <div class="card-actions">
                    <span class="text-muted small">{{ $pjlpList->count() }} peserta</span>
                </div>
            </div>
            @if($pjlpList->isEmpty())
            <div class="card-body">
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-heartbeat" style="font-size:2.5rem;"></i></div>
                    <p class="empty-title">Tidak ada data</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Unit</th>
                            <th class="text-center">Kehadiran</th>
                            <th class="text-center">Status</th>
                            <th>Tanggal Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pjlpList as $p)
                        @php
                            $hadirs  = $kehadiranRaw[$p->id] ?? collect();
                            $count   = $hadirs->count();
                            $cukup   = $count >= 2;
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $p->nama }}</td>
                            <td>
                                <span class="badge {{ $p->unit->value === 'security' ? 'bg-purple-lt text-purple' : 'bg-teal-lt text-teal' }}">
                                    {{ $p->unit->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="h4 mb-0 {{ $cukup ? 'text-success' : ($count == 1 ? 'text-warning' : 'text-danger') }}">
                                    {{ $count }}x
                                </span>
                            </td>
                            <td class="text-center">
                                @if($cukup)
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Terpenuhi</span>
                                @elseif($count == 1)
                                <span class="badge bg-warning-lt text-warning"><i class="ti ti-clock me-1"></i>Kurang 1x</span>
                                @else
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-x me-1"></i>Belum Hadir</span>
                                @endif
                            </td>
                            <td>
                                @forelse($hadirs as $h)
                                <span class="badge bg-blue-lt text-blue me-1">
                                    {{ $h->tanggal->translatedFormat('d M') }}
                                    @if($h->waktu) · {{ \Carbon\Carbon::parse($h->waktu)->format('H:i') }} @endif
                                </span>
                                @empty
                                <span class="text-muted small">—</span>
                                @endforelse
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
