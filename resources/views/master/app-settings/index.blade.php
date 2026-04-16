@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Administrasi</div>
                <h2 class="page-title">Pengaturan Sistem</h2>
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

        {{-- Window Input Jadwal CS --}}
        <div class="card mb-3" style="max-width:600px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-calendar-stats me-2 text-blue"></i>Window Input Jadwal CS & Security
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Secara default, input jadwal CS & Security hanya dibuka pada:
                    <ul class="mb-1 small">
                        <li><strong>Tanggal 25–akhir bulan</strong> → mengisi jadwal bulan depan</li>
                        <li><strong>Tanggal 1–5</strong> → revisi jadwal bulan lalu</li>
                    </ul>
                    Gunakan override ini untuk keperluan darurat (lupa input, koreksi mendadak, dll).
                </p>

                @php
                    $statusBg = match($jadwalCsOverride) {
                        'open'   => '#2fb344',
                        'closed' => '#d63939',
                        default  => '#206bc4',
                    };
                    $statusLabel = match($jadwalCsOverride) {
                        'open'   => 'Paksa Buka',
                        'closed' => 'Paksa Tutup',
                        default  => 'Otomatis (ikuti tanggal)',
                    };
                    $statusIcon = match($jadwalCsOverride) {
                        'open'   => 'lock-open',
                        'closed' => 'lock',
                        default  => 'settings-automation',
                    };
                @endphp

                <div class="mb-3">
                    <div class="text-muted small mb-1">Status saat ini</div>
                    <span class="badge fs-6 px-3 py-2" style="background-color:{{ $statusBg }};color:#fff;">
                        <i class="ti ti-{{ $statusIcon }} me-1"></i>
                        {{ $statusLabel }}
                    </span>
                </div>

                <form method="POST" action="{{ route('master.app-settings.jadwal-cs-window') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Ubah ke</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="override" value="auto" class="form-selectgroup-input"
                                       {{ $jadwalCsOverride === 'auto' ? 'checked' : '' }}>
                                <span class="form-selectgroup-label d-flex align-items-center gap-2">
                                    <i class="ti ti-settings-automation fs-3"></i>
                                    <span>
                                        <strong>Otomatis</strong><br>
                                        <small class="text-muted">Ikuti aturan tanggal</small>
                                    </span>
                                </span>
                            </label>
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="override" value="open" class="form-selectgroup-input"
                                       {{ $jadwalCsOverride === 'open' ? 'checked' : '' }}>
                                <span class="form-selectgroup-label d-flex align-items-center gap-2">
                                    <i class="ti ti-lock-open fs-3 text-success"></i>
                                    <span>
                                        <strong class="text-success">Paksa Buka</strong><br>
                                        <small class="text-muted">Semua bulan bisa diedit</small>
                                    </span>
                                </span>
                            </label>
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="override" value="closed" class="form-selectgroup-input"
                                       {{ $jadwalCsOverride === 'closed' ? 'checked' : '' }}>
                                <span class="form-selectgroup-label d-flex align-items-center gap-2">
                                    <i class="ti ti-lock fs-3 text-danger"></i>
                                    <span>
                                        <strong class="text-danger">Paksa Tutup</strong><br>
                                        <small class="text-muted">Kunci semua input</small>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Yakin ubah pengaturan window jadwal CS?')">
                        <i class="ti ti-device-floppy me-1"></i>Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
