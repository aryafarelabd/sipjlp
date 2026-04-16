@extends('layouts.app')

@section('title', 'Jadwal Kerja CS Bulanan')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Jadwal Kerja CS Bulanan
                </h2>
                <div class="text-muted mt-1">
                    Input jadwal pekerjaan CS per area per bulan (dikelola oleh Koordinator)
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.partials.jadwal-cs-tabs')

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('jadwal-kerja-cs-bulanan.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select name="area_id" class="form-select" onchange="this.form.submit()">
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>
                                    {{ $area->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-5 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Keterangan Warna Shift -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="fw-bold me-2">Keterangan Warna Shift:</span>
                    <span class="badge" style="background-color: #cce5ff; color: #004085;">Shift Pagi</span>
                    <span class="badge" style="background-color: #fff3cd; color: #856404;">Shift Siang</span>
                    <span class="badge" style="background-color: #f8c8dc; color: #721c24;">Shift Malam</span>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Jadwal {{ $selectedArea?->nama ?? '-' }} -
                    {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">Tgl</th>
                                <th class="text-center" style="width: 80px;">Hari</th>
                                <th>Pekerjaan</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $currentDate = \Carbon\Carbon::create($tahun, $bulan, $day);
                                    $dateKey = $currentDate->format('Y-m-d');
                                    $dayJadwals = $jadwals[$dateKey] ?? collect();
                                    $isToday = $currentDate->isToday();
                                    $isWeekend = $currentDate->isWeekend();
                                @endphp
                                <tr class="{{ $isToday ? 'table-info' : ($isWeekend ? 'table-secondary' : '') }}">
                                    <td class="text-center fw-bold">{{ $day }}</td>
                                    <td class="text-center">{{ $currentDate->translatedFormat('D') }}</td>
                                    <td class="p-1">
                                        @if($dayJadwals->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($dayJadwals as $jadwal)
                                                    <span class="badge" style="background-color: {{ $jadwal->shift_color }}; color: #333;">
                                                        {{ strtoupper($jadwal->nama_pekerjaan) }}
                                                        <small>({{ $jadwal->shift?->nama ?? '-' }})</small>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('jadwal-kerja-cs-bulanan.create', ['tanggal' => $dateKey, 'area_id' => $areaId]) }}"
                                           class="btn btn-sm btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            Kelola
                                        </a>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
