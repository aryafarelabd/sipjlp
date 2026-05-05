@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $isSecurity = $unit && $unit->value === 'security';
    $isCleaning = $unit && $unit->value === 'cleaning';
    $isChief    = auth()->user()->hasRole('chief');
    $isPjCs     = auth()->user()->hasRole('pj_cs');
@endphp

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Selamat Datang, {{ auth()->user()->name }}!</h2>
            </div>
            <div class="col-auto ms-auto">
                <span class="badge bg-blue-lt fs-6">
                    <i class="ti ti-building me-1"></i>
                    Unit {{ $unit ? $unit->label() : 'Semua' }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Welcome Card --}}
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="avatar avatar-lg bg-white-lt">
                            <i class="ti ti-calendar-event fs-1"></i>
                        </span>
                    </div>
                    <div class="col">
                        <h3 class="mb-1 text-white">{{ now()->translatedFormat('l, d F Y') }}</h3>
                        <div class="text-white-50">Kelola jadwal dan pantau kinerja PJLP unit Anda dengan mudah.</div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        @if($isSecurity && ($isChief || auth()->user()->hasRole('koordinator')))
                            <a href="{{ route('jadwal-security.index') }}" class="btn btn-light">
                                <i class="ti ti-calendar me-1"></i> Jadwal Security
                            </a>
                        @endif
                        @if($isCleaning && auth()->user()->can('jadwal-cs.manage'))
                            <a href="{{ route('jadwal-shift-cs.index') }}" class="btn btn-light">
                                <i class="ti ti-calendar me-1"></i> Jadwal CS
                            </a>
                        @endif
                        <a href="{{ route('absensi.rekap') }}" class="btn btn-light">
                            <i class="ti ti-report me-1"></i> Rekap Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-primary text-white avatar"><i class="ti ti-users"></i></span></div>
                            <div class="col">
                                <div class="font-weight-medium">Total PJLP</div>
                                <div class="text-muted">{{ $pjlpAktif }} aktif dari {{ $totalPjlp }}</div>
                            </div>
                            <div class="col-auto"><span class="h1 mb-0">{{ $totalPjlp }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-green text-white avatar"><i class="ti ti-fingerprint"></i></span></div>
                            <div class="col">
                                <div class="font-weight-medium">Hadir Hari Ini</div>
                                <div class="text-muted small">
                                    @if($absensiAlphaHariIni > 0)
                                        <span class="text-danger">{{ $absensiAlphaHariIni }} alpha</span> &bull;
                                    @endif
                                    {{ $pjlpAktif - $absensiMasukHariIni }} belum
                                </div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('absensi.rekap') }}" class="text-decoration-none">
                                    <span class="h1 mb-0 text-green">{{ $absensiMasukHariIni }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-warning text-white avatar"><i class="ti ti-calendar-off"></i></span></div>
                            <div class="col">
                                <div class="font-weight-medium">Cuti Menunggu</div>
                                <div class="text-muted">
                                    <a href="{{ route('cuti.index') }}?status=menunggu">Perlu persetujuan</a>
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="h1 mb-0 {{ $cutiPending > 0 ? 'text-warning' : '' }}">{{ $cutiPending }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if($isCleaning)
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-orange text-white avatar"><i class="ti ti-photo-check"></i></span></div>
                            <div class="col">
                                <div class="font-weight-medium">Bukti Pending</div>
                                <div class="text-muted">
                                    <a href="{{ route('lembar-kerja-cs.rekap') }}">Perlu validasi</a>
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="h1 mb-0 {{ $buktiPendingCount > 0 ? 'text-orange' : '' }}">{{ $buktiPendingCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-purple text-white avatar"><i class="ti ti-shield-check"></i></span></div>
                            <div class="col">
                                <div class="font-weight-medium">Rekap Laporan</div>
                                <div class="text-muted"><a href="{{ route('patrol-inspeksi.rekap') }}">Lihat semua</a></div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('patrol-inspeksi.rekap') }}" class="btn btn-sm btn-outline-purple">Lihat</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Aksi Cepat --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-bolt me-2 text-yellow"></i>Aksi Cepat</h3>
            </div>
            <div class="card-body">

                {{-- Umum --}}
                <div class="text-muted small fw-bold mb-2 text-uppercase">Umum</div>
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('absensi.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-green-lt mb-2 mx-auto"><i class="ti ti-fingerprint fs-3"></i></span>
                            <div class="fw-medium small">Rekap Absensi</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('cuti.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-pink-lt mb-2 mx-auto"><i class="ti ti-plane fs-3"></i></span>
                            <div class="fw-medium small">Data Cuti</div>
                        </a>
                    </div>
                    @if(auth()->user()->can('laporan.view'))
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('gerakan-jumat-sehat.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-pink-lt mb-2 mx-auto"><i class="ti ti-heartbeat fs-3"></i></span>
                            <div class="fw-medium small">Rekap Jumat Sehat</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('laporan-kecelakaan.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-danger-lt mb-2 mx-auto"><i class="ti ti-alert-triangle fs-3"></i></span>
                            <div class="fw-medium small">Rekap K3</div>
                        </a>
                    </div>
                    @endif
                </div>

                @if($isCleaning)
                {{-- Cleaning Service --}}
                <div class="text-muted small fw-bold mb-2 text-uppercase">Cleaning Service</div>
                <div class="row g-2">
                    @if(auth()->user()->can('jadwal-cs.manage'))
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('jadwal-shift-cs.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-cyan-lt mb-2 mx-auto"><i class="ti ti-calendar fs-3"></i></span>
                            <div class="fw-medium small">Jadwal CS</div>
                        </a>
                    </div>
                    @endif
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('lembar-kerja-cs.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-cyan-lt mb-2 mx-auto"><i class="ti ti-file-text fs-3"></i></span>
                            <div class="fw-medium small">Lembar Kerja CS</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('lembar-kerja-cs.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-orange-lt mb-2 mx-auto"><i class="ti ti-checkbox fs-3"></i></span>
                            <div class="fw-medium small">Validasi Bukti CS</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-limbah.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-orange-lt mb-2 mx-auto"><i class="ti ti-file-invoice fs-3"></i></span>
                            <div class="fw-medium small">Rekap Limbah</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-b3.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-yellow-lt mb-2 mx-auto"><i class="ti ti-biohazard fs-3"></i></span>
                            <div class="fw-medium small">Rekap B3</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-hepafilter.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-teal-lt mb-2 mx-auto"><i class="ti ti-filter fs-3"></i></span>
                            <div class="fw-medium small">Rekap Hepafilter</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-dekontaminasi.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-indigo-lt mb-2 mx-auto"><i class="ti ti-wind fs-3"></i></span>
                            <div class="fw-medium small">Rekap Dekontaminasi</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('logbook-bank-sampah.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-lime-lt mb-2 mx-auto"><i class="ti ti-recycle fs-3"></i></span>
                            <div class="fw-medium small">Rekap Bank Sampah</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('master.kegiatan-lk-cs.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-purple-lt mb-2 mx-auto"><i class="ti ti-clipboard-list fs-3"></i></span>
                            <div class="fw-medium small">Kegiatan LK CS</div>
                        </a>
                    </div>
                </div>

                @else
                {{-- Security --}}
                <div class="text-muted small fw-bold mb-2 text-uppercase">Security</div>
                <div class="row g-2">
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('jadwal-security.index') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-blue-lt mb-2 mx-auto"><i class="ti ti-calendar-event fs-3"></i></span>
                            <div class="fw-medium small">Jadwal Security</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('patrol-inspeksi.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-purple-lt mb-2 mx-auto"><i class="ti ti-shield-check fs-3"></i></span>
                            <div class="fw-medium small">Rekap Patrol</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('inspeksi-hydrant.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-red-lt mb-2 mx-auto"><i class="ti ti-flame fs-3"></i></span>
                            <div class="fw-medium small">Rekap Hydrant Outdoor</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('inspeksi-hydrant-indoor.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-orange-lt mb-2 mx-auto"><i class="ti ti-flame-off fs-3"></i></span>
                            <div class="fw-medium small">Rekap Hydrant Indoor</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('pengecekan-apar.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-yellow-lt mb-2 mx-auto"><i class="ti ti-fire-extinguisher fs-3"></i></span>
                            <div class="fw-medium small">Rekap APAR & APAB</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('pengawasan-proyek.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-teal-lt mb-2 mx-auto"><i class="ti ti-building-factory-2 fs-3"></i></span>
                            <div class="fw-medium small">Rekap Pengawasan Proyek</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('laporan-parkir.rekap') }}" class="card card-link card-link-pop text-center p-3 h-100">
                            <span class="avatar avatar-md bg-blue-lt mb-2 mx-auto"><i class="ti ti-car fs-3"></i></span>
                            <div class="fw-medium small">Rekap Laporan Parkir</div>
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="row row-deck row-cards">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-calendar-off me-2 text-warning"></i>Pengajuan Cuti Terbaru</h3>
                        <div class="card-actions">
                            <a href="{{ route('cuti.index') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body card-body-scrollable card-body-scrollable-shadow" style="max-height: 350px;">
                        <div class="divide-y">
                            @forelse($recentCuti as $cuti)
                            <div class="row py-2">
                                <div class="col-auto">
                                    <span class="avatar bg-{{ $cuti->status->color() }}-lt">
                                        {{ strtoupper(substr($cuti->pjlp->nama, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="text-truncate fw-medium">{{ $cuti->pjlp->nama }}</div>
                                    <div class="text-muted small">
                                        {{ $cuti->jenisCuti->nama }} &bull;
                                        {{ $cuti->tgl_mulai->format('d/m') }} - {{ $cuti->tgl_selesai->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div class="col-auto align-self-center">
                                    <span class="badge bg-{{ $cuti->status->color() }}-lt">{{ $cuti->status->label() }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="empty py-4">
                                <div class="empty-icon"><i class="ti ti-mood-smile"></i></div>
                                <p class="empty-title">Tidak ada pengajuan cuti</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            @if($isCleaning)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-photo-check me-2 text-orange"></i>Bukti Menunggu Validasi</h3>
                        <div class="card-actions">
                            <a href="{{ route('lembar-kerja-cs.rekap') }}" class="btn btn-sm btn-outline-warning">
                                Validasi <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body card-body-scrollable card-body-scrollable-shadow" style="max-height: 350px;">
                        <div class="divide-y">
                            @forelse($recentBuktiPending as $bukti)
                            <div class="row py-2">
                                <div class="col-auto">
                                    <span class="avatar bg-orange-lt"><i class="ti ti-photo"></i></span>
                                </div>
                                <div class="col">
                                    <div class="text-truncate fw-medium">{{ $bukti->pjlp->nama ?? '-' }}</div>
                                    <div class="text-muted small">
                                        {{ $bukti->jadwalBulanan->pekerjaan ?? '-' }}
                                        <br>
                                        <span class="text-primary">{{ $bukti->jadwalBulanan->area->nama ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-auto align-self-center text-end">
                                    <div class="small text-muted">
                                        {{ $bukti->dikerjakan_at ? $bukti->dikerjakan_at->diffForHumans() : '-' }}
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="empty py-4">
                                <div class="empty-icon"><i class="ti ti-mood-smile"></i></div>
                                <p class="empty-title">Semua bukti sudah divalidasi</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-shield-check me-2 text-purple"></i>Laporan Security Terbaru</h3>
                        <div class="card-actions">
                            <a href="{{ route('patrol-inspeksi.rekap') }}" class="btn btn-sm btn-outline-purple">
                                Lihat Semua <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="divide-y">
                            @forelse($recentBuktiPending as $laporan)
                            <div class="row py-2">
                                <div class="col-auto">
                                    <span class="avatar bg-purple-lt"><i class="ti ti-shield"></i></span>
                                </div>
                                <div class="col">
                                    <div class="text-truncate fw-medium">{{ $laporan->pjlp->nama ?? '-' }}</div>
                                    <div class="text-muted small">{{ $laporan->created_at?->diffForHumans() ?? '-' }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="empty py-4">
                                <div class="empty-icon"><i class="ti ti-mood-smile"></i></div>
                                <p class="empty-title">Belum ada laporan patrol</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
