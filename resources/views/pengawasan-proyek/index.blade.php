@extends('layouts.app')

@section('title', 'Pengawasan Proyek')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Pengawasan Pembangunan &amp; Aktivitas Proyek</h2>
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

        <div class="alert alert-info mb-3">
            <i class="ti ti-info-circle me-2"></i>
            Pengisian laporan diharapkan sesuai jam dinas yang ditentukan.
            <div class="mt-1 small">
                <span class="badge bg-blue-lt text-blue me-1">Shift Pagi 07.00–14.00</span>
                <span class="badge bg-orange-lt text-orange me-1">Shift Siang 14.00–21.00</span>
                <span class="badge bg-dark-lt text-dark me-1">Shift Malam 20.30–07.30</span>
            </div>
        </div>

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ──────────────────────────────── --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('pengawasan-proyek.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Header --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Pengawasan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="tanggal"
                                   class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Shift Petugas</label>
                            <select name="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Shift --</option>
                                @foreach($shifts as $s)
                                <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }}
                                    ({{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }})
                                </option>
                                @endforeach
                            </select>
                            @error('shift_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Nama Proyek</label>
                            <input type="text" name="nama_proyek"
                                   class="form-control @error('nama_proyek') is-invalid @enderror"
                                   value="{{ old('nama_proyek') }}"
                                   placeholder="Contoh: Pembangunan Gedung IGD Baru" required>
                            @error('nama_proyek')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">Lokasi</label>
                            <input type="text" name="lokasi"
                                   class="form-control @error('lokasi') is-invalid @enderror"
                                   value="{{ old('lokasi') }}"
                                   placeholder="Contoh: Lantai 2, Gedung Utara" required>
                            @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Seksi Checklist --}}
                @foreach(\App\Models\PengawasanProyek::SEKSI as $seksiKey => $seksiConfig)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-clipboard-check me-2"></i>{{ $seksiConfig['label'] }}
                        </h3>
                    </div>
                    <div class="card-body">

                        {{-- Table YA/TIDAK --}}
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Pernyataan</th>
                                        <th width="60" class="text-center">YA</th>
                                        <th width="70" class="text-center">TIDAK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seksiConfig['items'] as $itemKey => $itemLabel)
                                    @php $oldVal = old("{$seksiKey}.items.{$itemKey}"); @endphp
                                    <tr>
                                        <td class="small">{{ $itemLabel }}</td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   class="form-check-input"
                                                   name="{{ $seksiKey }}[items][{{ $itemKey }}]"
                                                   value="ya" required
                                                   {{ $oldVal === 'ya' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   class="form-check-input"
                                                   name="{{ $seksiKey }}[items][{{ $itemKey }}]"
                                                   value="tidak"
                                                   {{ $oldVal === 'tidak' ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Upaya Perbaikan --}}
                        <div class="mb-0">
                            <label class="form-label required small fw-medium">Upaya Perbaikan</label>
                            <textarea name="{{ $seksiKey }}[upaya_perbaikan]"
                                      class="form-control form-control-sm"
                                      rows="2"
                                      placeholder="Uraikan upaya perbaikan yang dilakukan..." required>{{ old("{$seksiKey}.upaya_perbaikan") }}</textarea>
                        </div>

                    </div>
                </div>
                @endforeach

                {{-- Foto --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-camera me-1"></i>Dokumentasi Foto</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label class="form-label small">Foto Temuan (opsional, maks 10 MB)</label>
                            <input type="file" name="foto"
                                   class="form-control @error('foto') is-invalid @enderror"
                                   accept="image/*">
                            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Laporan Pengawasan Proyek
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Laporan
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('pengawasan-proyek.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-building-factory-2" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada laporan</p>
                            <p class="empty-subtitle text-muted">Gunakan form di sebelah kiri untuk menambah laporan.</p>
                        </div>
                    </div>
                    @else
                    <div class="card-body border-bottom py-2 text-center">
                        <span class="text-muted small">{{ $riwayat->count() }} laporan bulan ini</span>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" style="max-height:75vh;">
                        @foreach($riwayat as $log)
                        @php $pct = $log->persentase; @endphp
                        <a href="{{ route('pengawasan-proyek.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge {{ $pct >= 80 ? 'bg-success-lt text-success' : ($pct >= 50 ? 'bg-warning-lt text-warning' : 'bg-danger-lt text-danger') }}">
                                    {{ $pct }}% Patuh
                                </span>
                            </div>
                            <div class="text-muted small text-truncate">{{ $log->nama_proyek }}</div>
                            <div class="text-muted small">{{ $log->shift->nama ?? '-' }}</div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
