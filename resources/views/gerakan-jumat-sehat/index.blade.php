@extends('layouts.app')

@section('title', 'Gerakan Jumat Sehat')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ auth()->user()->pjlp?->unit?->label() ?? 'PJLP' }}</div>
                <h2 class="page-title">Gerakan Jumat Sehat</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        {{-- Status kehadiran bulan ini --}}
        <div class="alert {{ $kehadiranBulanIni >= 2 ? 'alert-success' : ($kehadiranBulanIni == 1 ? 'alert-warning' : 'alert-danger') }} mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="ti {{ $kehadiranBulanIni >= 2 ? 'ti-circle-check' : 'ti-alert-triangle' }} flex-shrink-0"></i>
                <div>
                    <strong>Kehadiran bulan ini: {{ $kehadiranBulanIni }}x</strong>
                    @if($kehadiranBulanIni >= 2)
                    — Sudah memenuhi kewajiban minimal 2x. Tetap semangat!
                    @elseif($kehadiranBulanIni == 1)
                    — Masih kurang 1x kehadiran dari minimal 2x.
                    @else
                    — Belum ada kehadiran bulan ini. Wajib hadir minimal 2x!
                    @endif
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-3">
            <i class="ti ti-info-circle me-2"></i>
            Kegiatan Gerakan Jumat Sehat wajib dilakukan oleh seluruh anggota yang sedang libur, dinas siang, dan malam.
            Kehadiran wajib <strong>minimal 2x dalam sebulan</strong>.
        </div>

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-heartbeat me-1"></i>Catat Kehadiran</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('gerakan-jumat-sehat.store') }}" enctype="multipart/form-data">
                        @csrf
                            <div class="mb-3">
                                <label class="form-label required">Tanggal Kehadiran</label>
                                <input type="date" name="tanggal"
                                       class="form-control @error('tanggal') is-invalid @enderror"
                                       value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Waktu Kehadiran</label>
                                <input type="time" name="waktu"
                                       class="form-control @error('waktu') is-invalid @enderror"
                                       value="{{ old('waktu') }}">
                                @error('waktu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label required">Foto Bukti Kehadiran <small class="text-muted">(maks 10 MB)</small></label>
                                <input type="file" name="foto"
                                       class="form-control @error('foto') is-invalid @enderror"
                                       accept="image/*" required>
                                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-check me-1"></i>Catat Kehadiran
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-history me-1"></i>Riwayat Kehadiran</h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('gerakan-jumat-sehat.index') }}"
                                  class="d-flex gap-2 align-items-center">
                                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                    </option>
                                    @endfor
                                </select>
                                <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </form>
                        </div>
                    </div>

                    @if($riwayat->isEmpty())
                    <div class="card-body">
                        <div class="empty py-4">
                            <div class="empty-icon"><i class="ti ti-heartbeat" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada kehadiran</p>
                            <p class="empty-subtitle text-muted">Catat kehadiran Jumat Sehat bulan ini.</p>
                        </div>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayat as $i => $r)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $r->tanggal->translatedFormat('d M Y') }}</div>
                                        <small class="text-muted">{{ $r->tanggal->translatedFormat('l') }}</small>
                                    </td>
                                    <td>{{ $r->waktu ? \Carbon\Carbon::parse($r->waktu)->format('H:i') : '—' }}</td>
                                    <td>
                                        <a href="{{ $r->foto_url }}" target="_blank">
                                            <img src="{{ $r->foto_url }}" alt="foto"
                                                 class="rounded" style="height:48px;width:48px;object-fit:cover;">
                                        </a>
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
    </div>
</div>
@endsection
