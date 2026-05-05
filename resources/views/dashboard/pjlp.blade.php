@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Selamat Datang</div>
                <h2 class="page-title">{{ auth()->user()->name }}</h2>
            </div>
            @if($pjlp)
            <div class="col-auto">
                <span class="badge bg-green-lt fs-6">
                    <i class="ti ti-id-badge me-1"></i>{{ $pjlp->unit->label() }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(!$pjlp)
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle icon alert-icon"></i>
            <h4 class="alert-title">Profil PJLP Belum Terdaftar</h4>
            <div class="text-muted">Akun Anda belum terhubung dengan data PJLP. Silakan hubungi Administrator.</div>
        </div>
        @else

        {{-- ── Welcome Card ──────────────────────────────────────────── --}}
        @php $isCleaning = $pjlp->unit === \App\Enums\UnitType::CLEANING; @endphp
        <div class="card mb-4" style="background: linear-gradient(135deg, {{ $isCleaning ? '#2fb344, #1a7a2e' : '#4a4a8a, #2d2d5a' }});">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        @if($pjlp->foto)
                        <span class="avatar avatar-xl" style="background-image: url({{ asset('storage/pjlp/' . $pjlp->foto) }})"></span>
                        @else
                        <span class="avatar avatar-xl bg-white-lt">
                            <i class="ti ti-user fs-1"></i>
                        </span>
                        @endif
                    </div>
                    <div class="col">
                        <h3 class="mb-1 text-white">{{ now()->translatedFormat('l, d F Y') }}</h3>
                        <div class="text-white-50 small">NIP: {{ $pjlp->nip }} &bull; {{ $pjlp->unit->label() }}</div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="text-white-50 small mb-1">Shift Hari Ini</div>
                        @if($jadwalShiftHariIni)
                            @if($jadwalShiftHariIni->status === 'normal')
                            <span class="badge bg-white text-dark fs-5 py-2 px-3">
                                <i class="ti ti-clock me-1"></i>{{ $jadwalShiftHariIni->shift->nama ?? '-' }}
                            </span>
                            @else
                            <span class="badge bg-{{ $jadwalShiftHariIni->status_color }} fs-5 py-2 px-3">
                                {{ $jadwalShiftHariIni->status_label }}
                            </span>
                            @endif
                        @else
                        <span class="badge bg-secondary text-white fs-5 py-2 px-3">Belum Ada Jadwal</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ─────────────────────────────────────────────── --}}
        <div class="row row-deck row-cards mb-4">
            {{-- Absensi --}}
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar"><i class="ti ti-fingerprint"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-medium">Hari Hadir</div>
                                <div class="text-muted small">
                                    @if(($rekapAbsensiBulanIni['total_alpha'] ?? 0) > 0)
                                        <span class="text-danger">{{ $rekapAbsensiBulanIni['total_alpha'] }} alpha</span>
                                    @else
                                        Bulan ini
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('absen.index') }}" class="text-decoration-none">
                                    <span class="h2 mb-0 text-green">{{ $rekapAbsensiBulanIni['hari_masuk'] ?? 0 }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jumat Sehat --}}
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="{{ $jumatSehatCount >= 2 ? 'bg-pink' : 'bg-orange' }} text-white avatar">
                                    <i class="ti ti-heartbeat"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="fw-medium">Jumat Sehat</div>
                                <div class="text-muted small">
                                    {{ $jumatSehatCount >= 2 ? 'Terpenuhi ✓' : 'Belum cukup' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 {{ $jumatSehatCount >= 2 ? 'text-pink' : 'text-orange' }}">
                                    {{ $jumatSehatCount }}x
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cuti Pending --}}
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-orange text-white avatar"><i class="ti ti-hourglass"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-medium">Cuti Pending</div>
                                <div class="text-muted small">Menunggu</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 text-orange">{{ $cutiPending->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isCleaning)
            {{-- Total Logbook CS --}}
            @php $totalLogbook = array_sum($unitStats); @endphp
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-cyan text-white avatar"><i class="ti ti-file-invoice"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-medium">Total Logbook</div>
                                <div class="text-muted small">Bulan ini</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 text-cyan">{{ $totalLogbook }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Total Laporan Security --}}
            @php $totalLaporan = array_sum($unitStats); @endphp
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-purple text-white avatar"><i class="ti ti-shield-check"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-medium">Total Laporan</div>
                                <div class="text-muted small">Bulan ini</div>
                            </div>
                            <div class="col-auto">
                                <span class="h2 mb-0 text-purple">{{ $totalLaporan }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── Aksi Cepat ─────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-bolt me-2 text-yellow"></i>Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <div class="row g-2">

                    {{-- Absen (semua) --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('absen.index') }}" class="card card-link card-link-pop text-center p-3 border-success h-100">
                            <span class="avatar avatar-md bg-success-lt mb-2 mx-auto"><i class="ti ti-fingerprint fs-3"></i></span>
                            <div class="fw-medium small text-success">Absen</div>
                        </a>
                    </div>

                    @if($isCleaning)
                    {{-- CLEANING SERVICE --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('lembar-kerja-cs.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-cyan-lt mb-2 mx-auto"><i class="ti ti-file-text fs-3"></i></span>
                            <div class="fw-medium small">Lembar Kerja</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-limbah.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-orange-lt mb-2 mx-auto"><i class="ti ti-file-invoice fs-3"></i></span>
                            <div class="fw-medium small">Logbook Limbah</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-b3.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-yellow-lt mb-2 mx-auto"><i class="ti ti-biohazard fs-3"></i></span>
                            <div class="fw-medium small">Logbook B3</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-hepafilter.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-teal-lt mb-2 mx-auto"><i class="ti ti-filter fs-3"></i></span>
                            <div class="fw-medium small">Hepafilter</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-dekontaminasi.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-indigo-lt mb-2 mx-auto"><i class="ti ti-wind fs-3"></i></span>
                            <div class="fw-medium small">Dekontaminasi</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-bank-sampah.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-lime-lt mb-2 mx-auto"><i class="ti ti-recycle fs-3"></i></span>
                            <div class="fw-medium small">Bank Sampah</div>
                        </a>
                    </div>

                    @else
                    {{-- SECURITY --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('patrol-inspeksi.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-purple-lt mb-2 mx-auto"><i class="ti ti-shield-check fs-3"></i></span>
                            <div class="fw-medium small">Patrol Inspeksi</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('inspeksi-hydrant.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-red-lt mb-2 mx-auto"><i class="ti ti-flame fs-3"></i></span>
                            <div class="fw-medium small">Hydrant Outdoor</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('inspeksi-hydrant-indoor.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-orange-lt mb-2 mx-auto"><i class="ti ti-flame-off fs-3"></i></span>
                            <div class="fw-medium small">Hydrant Indoor</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('pengecekan-apar.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-yellow-lt mb-2 mx-auto"><i class="ti ti-fire-extinguisher fs-3"></i></span>
                            <div class="fw-medium small">APAR & APAB</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('pengawasan-proyek.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-teal-lt mb-2 mx-auto"><i class="ti ti-building-factory-2 fs-3"></i></span>
                            <div class="fw-medium small">Pengawasan Proyek</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('laporan-kecelakaan.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-danger-lt mb-2 mx-auto"><i class="ti ti-alert-triangle fs-3"></i></span>
                            <div class="fw-medium small">Laporan Kecelakaan</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('laporan-parkir.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-blue-lt mb-2 mx-auto"><i class="ti ti-car fs-3"></i></span>
                            <div class="fw-medium small">Laporan Parkir</div>
                        </a>
                    </div>
                    @endif

                    {{-- Jumat Sehat (semua) --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('gerakan-jumat-sehat.index') }}" class="card card-link card-link-pop text-center p-3 h-100 {{ $jumatSehatCount < 2 ? 'border-warning' : '' }}">
                            <span class="avatar avatar-md bg-pink-lt mb-2 mx-auto"><i class="ti ti-heartbeat fs-3"></i></span>
                            <div class="fw-medium small">Jumat Sehat</div>
                            @if($jumatSehatCount < 2)
                            <div class="badge bg-warning-lt text-warning mt-1" style="font-size:10px;">{{ $jumatSehatCount }}/2x</div>
                            @endif
                        </a>
                    </div>

                    {{-- Cuti (semua) --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('cuti.create') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-primary-lt mb-2 mx-auto"><i class="ti ti-plane-departure fs-3"></i></span>
                            <div class="fw-medium small">Ajukan Cuti</div>
                        </a>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Unit Stats Detail ──────────────────────────────────────── --}}
        <div class="row row-deck row-cards mb-4">

            {{-- Aktivitas Modul Bulan Ini --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-chart-bar me-2 {{ $isCleaning ? 'text-cyan' : 'text-purple' }}"></i>
                            Aktivitas Bulan Ini
                        </h3>
                        <div class="card-actions">
                            <span class="text-muted small">{{ now()->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($isCleaning)
                        @php
                        $csModules = [
                            ['label' => 'Logbook Limbah',     'count' => $unitStats['logbook_limbah'],      'icon' => 'ti-file-invoice', 'color' => 'orange'],
                            ['label' => 'Logbook B3',         'count' => $unitStats['logbook_b3'],          'icon' => 'ti-biohazard',    'color' => 'yellow'],
                            ['label' => 'Logbook Hepafilter', 'count' => $unitStats['logbook_hepafilter'],  'icon' => 'ti-filter',       'color' => 'teal'],
                            ['label' => 'Dekontaminasi Udara','count' => $unitStats['logbook_dekont'],       'icon' => 'ti-wind',         'color' => 'indigo'],
                            ['label' => 'Bank Sampah',        'count' => $unitStats['logbook_bank_sampah'], 'icon' => 'ti-recycle',      'color' => 'lime'],
                        ];
                        @endphp
                        <div class="divide-y">
                            @foreach($csModules as $m)
                            <div class="row py-2 align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm bg-{{ $m['color'] }}-lt">
                                        <i class="ti {{ $m['icon'] }}"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="small fw-medium">{{ $m['label'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge {{ $m['count'] > 0 ? 'bg-' . $m['color'] . '-lt text-' . $m['color'] : 'bg-secondary-lt text-muted' }}">
                                        {{ $m['count'] }} laporan
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        @php
                        $secModules = [
                            ['label' => 'Patrol Inspeksi',       'count' => $unitStats['patrol'],          'icon' => 'ti-shield-check',       'color' => 'purple'],
                            ['label' => 'Hydrant Outdoor',       'count' => $unitStats['hydrant_outdoor'], 'icon' => 'ti-flame',              'color' => 'red'],
                            ['label' => 'Hydrant Indoor',        'count' => $unitStats['hydrant_indoor'],  'icon' => 'ti-flame-off',          'color' => 'orange'],
                            ['label' => 'APAR & APAB',           'count' => $unitStats['apar'],            'icon' => 'ti-fire-extinguisher',  'color' => 'yellow'],
                            ['label' => 'Pengawasan Proyek',     'count' => $unitStats['proyek'],          'icon' => 'ti-building-factory-2', 'color' => 'teal'],
                            ['label' => 'Laporan Parkir',        'count' => $unitStats['parkir'] ?? 0,     'icon' => 'ti-car',                'color' => 'blue'],
                        ];
                        @endphp
                        <div class="divide-y">
                            @foreach($secModules as $m)
                            <div class="row py-2 align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm bg-{{ $m['color'] }}-lt">
                                        <i class="ti {{ $m['icon'] }}"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="small fw-medium">{{ $m['label'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge {{ $m['count'] > 0 ? 'bg-' . $m['color'] . '-lt text-' . $m['color'] : 'bg-secondary-lt text-muted' }}">
                                        {{ $m['count'] }} laporan
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Jumat Sehat row --}}
                        <div class="row py-2 align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-sm bg-pink-lt"><i class="ti ti-heartbeat"></i></span>
                            </div>
                            <div class="col">
                                <div class="small fw-medium">Jumat Sehat</div>
                            </div>
                            <div class="col-auto">
                                @if($jumatSehatCount >= 2)
                                <span class="badge bg-success-lt text-success">{{ $jumatSehatCount }}x — Terpenuhi</span>
                                @elseif($jumatSehatCount == 1)
                                <span class="badge bg-warning-lt text-warning">{{ $jumatSehatCount }}x — Kurang 1x</span>
                                @else
                                <span class="badge bg-danger-lt text-danger">0x — Belum hadir</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pengajuan Cuti --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-hourglass me-2 text-warning"></i>Pengajuan Cuti</h3>
                        <div class="card-actions">
                            <a href="{{ route('cuti.index') }}" class="btn btn-sm btn-outline-primary">
                                Semua <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body card-body-scrollable" style="max-height:220px;">
                        @if($cutiPending->count() > 0)
                        <div class="divide-y">
                            @foreach($cutiPending as $cuti)
                            <div class="row py-2">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm bg-warning-lt"><i class="ti ti-plane"></i></span>
                                </div>
                                <div class="col">
                                    <div class="small fw-medium text-truncate">{{ $cuti->jenisCuti->nama }}</div>
                                    <div class="text-muted" style="font-size:11px;">
                                        {{ $cuti->tgl_mulai->format('d/m') }} – {{ $cuti->tgl_selesai->format('d/m/Y') }}
                                        ({{ $cuti->jumlah_hari }} hari)
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-{{ $cuti->status->color() }}-lt small">{{ $cuti->status->label() }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty py-3">
                            <div class="empty-icon"><i class="ti ti-mood-smile"></i></div>
                            <p class="empty-title small">Tidak ada cuti menunggu</p>
                            <div class="empty-action">
                                <a href="{{ route('cuti.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i>Ajukan Cuti
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @endif
    </div>
</div>
@endsection
