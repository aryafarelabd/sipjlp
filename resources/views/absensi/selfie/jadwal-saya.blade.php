@extends('layouts.app')

@section('title', 'Jadwal Saya')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Jadwal Saya</h2>
                <div class="text-muted mt-1">{{ $pjlp->nama }} &bull; {{ ucfirst($pjlp->unit->value) }}</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('absen.index') }}" class="btn btn-primary">
                    <i class="ti ti-camera me-1"></i>Absen Sekarang
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter Bulan/Tahun --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('jadwal-saya.index') }}" class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label mb-1 small">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rekap ringkas --}}
        @php
            $totalKerja  = collect($hariList)->where('is_kerja', true)->count();
            $totalHadir  = collect($hariList)->filter(fn($h) => $h['absensi'] && $h['absensi']->jam_masuk)->count();
            $totalAlpha  = collect($hariList)->filter(fn($h) => $h['absensi'] && $h['absensi']->status?->value === 'alpha')->count();
            $totalLibur  = collect($hariList)->where('is_kerja', false)->where('tanggal', '<=', now())->count();
        @endphp
        <div class="row row-deck row-cards mb-3">
            <div class="col-6 col-sm-3">
                <div class="card card-sm text-center">
                    <div class="card-body">
                        <div class="text-muted small">Hari Kerja</div>
                        <div class="h2 mb-0 text-blue">{{ $totalKerja }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card card-sm text-center">
                    <div class="card-body">
                        <div class="text-muted small">Sudah Hadir</div>
                        <div class="h2 mb-0 text-success">{{ $totalHadir }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card card-sm text-center">
                    <div class="card-body">
                        <div class="text-muted small">Alpha</div>
                        <div class="h2 mb-0 {{ $totalAlpha > 0 ? 'text-danger' : 'text-muted' }}">{{ $totalAlpha }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card card-sm text-center">
                    <div class="card-body">
                        <div class="text-muted small">Libur/Off</div>
                        <div class="h2 mb-0 text-muted">{{ $totalLibur }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar hari --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-calendar me-1"></i>
                    {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
                </h3>
            </div>
            <div class="list-group list-group-flush">
                @foreach($hariList as $hari)
                @php
                    $tgl     = $hari['tanggal'];
                    $shift   = $hari['shift'];
                    $absensi = $hari['absensi'];
                    $isToday = $hari['is_today'];
                    $isPast  = $hari['is_past'];
                    $isKerja = $hari['is_kerja'];

                    // Warna baris
                    $rowClass = '';
                    if ($isToday) $rowClass = 'bg-azure-lt';
                    elseif ($tgl->isSunday()) $rowClass = 'bg-danger-lt';

                    // Status absensi
                    $statusValue = $absensi?->status?->value ?? '';
                    [$statusLabel, $statusClass] = match($statusValue) {
                        'hadir'     => ['Hadir',          'bg-success-lt text-success'],
                        'terlambat' => ['Terlambat',      'bg-warning-lt text-warning'],
                        'alpha'     => ['Alpha',          'bg-danger-lt text-danger'],
                        'izin'      => ['Izin',           'bg-info-lt text-info'],
                        'cuti'      => ['Cuti',           'bg-blue-lt text-blue'],
                        'sakit'     => ['Sakit',          'bg-secondary-lt text-secondary'],
                        default     => $absensi
                            ? [($statusValue ?: '-'), 'bg-secondary-lt text-secondary']
                            : (($isKerja && $isPast) ? ['Belum tercatat', 'bg-secondary-lt text-muted'] : ['', '']),
                    };
                @endphp
                <div class="list-group-item {{ $rowClass }}">
                    <div class="row align-items-center g-2">
                        {{-- Tanggal --}}
                        <div class="col-auto" style="min-width:56px;">
                            <div class="text-center">
                                <div class="small text-muted" style="font-size:0.7rem;">{{ $tgl->translatedFormat('D') }}</div>
                                <div class="fw-bold {{ $isToday ? 'text-azure' : ($tgl->isSunday() ? 'text-danger' : '') }}" style="font-size:1.1rem;line-height:1;">
                                    {{ $tgl->format('d') }}
                                </div>
                            </div>
                        </div>

                        {{-- Shift info --}}
                        <div class="col">
                            @if($isKerja && $shift)
                                @php
                                    $shiftBg = match(strtolower($shift->nama)) {
                                        'pagi'  => '#cce5ff', 'siang' => '#fff3cd', 'malam' => '#f8c8dc',
                                        default => '#e9ecef',
                                    };
                                    $shiftTc = match(strtolower($shift->nama)) {
                                        'pagi'  => '#004085', 'siang' => '#856404', 'malam' => '#721c47',
                                        default => '#495057',
                                    };
                                @endphp
                                <span class="badge fw-semibold" style="background-color:{{ $shiftBg }};color:{{ $shiftTc }};">
                                    {{ strtoupper($shift->nama) }}
                                </span>
                                <span class="text-muted small ms-1">
                                    {{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}
                                </span>
                                @if($hari['window_masuk'] && ($isToday || !$isPast))
                                <div class="text-muted" style="font-size:0.72rem;">
                                    Absen masuk: {{ $hari['window_masuk']['open']->format('H:i') }}–{{ $hari['window_masuk']['close']->format('H:i') }}
                                    &bull;
                                    Pulang: {{ $hari['window_pulang']['open']->format('H:i') }}–{{ $hari['window_pulang']['close']->format('H:i') }}
                                </div>
                                @endif
                            @elseif(!$isKerja && !$tgl->isSunday())
                                <span class="text-muted small">Libur / Off</span>
                            @elseif($tgl->isSunday())
                                <span class="text-muted small">Minggu</span>
                            @else
                                <span class="text-muted small">— Jadwal belum diinput —</span>
                            @endif
                        </div>

                        {{-- Absensi status --}}
                        <div class="col-auto">
                            @if($isKerja)
                                @if($statusLabel)
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                @endif
                                @if($absensi && $absensi->jam_masuk)
                                <div class="text-muted text-end" style="font-size:0.72rem;">
                                    {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }}
                                    @if($absensi->jam_pulang)
                                    – {{ \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') }}
                                    @endif
                                </div>
                                @endif
                            @endif
                        </div>

                        {{-- Today indicator --}}
                        @if($isToday)
                        <div class="col-auto">
                            <a href="{{ route('absen.index') }}" class="btn btn-sm btn-azure">
                                <i class="ti ti-camera me-1"></i>Absen
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
