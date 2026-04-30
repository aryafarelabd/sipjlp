@extends('layouts.app')

@section('title', 'Pemeriksaan Hydrant Outdoor')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Pemeriksaan Hydrant Outdoor</h2>
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

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ──────────────────────────────────── --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('inspeksi-hydrant.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Header --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Pemeriksaan</h3>
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
                        <div class="mb-0">
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
                    </div>
                </div>

                {{-- Per Lokasi --}}
                @foreach($lokasi as $lokKey => $lokLabel)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-flame me-2"></i>{{ $lokLabel }}
                        </h3>
                    </div>
                    <div class="card-body">
                        @foreach($komponen as $kompKey => $kompConfig)
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="form-label fw-medium required">{{ $kompConfig['label'] }}</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($kompConfig['options'] as $optVal => $optLabel)
                                <label class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input"
                                           name="lokasi[{{ $lokKey }}][{{ $kompKey }}]"
                                           value="{{ $optVal }}" required
                                           {{ old("lokasi.{$lokKey}.{$kompKey}") === $optVal ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ $optLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                            <input type="text"
                                   name="lokasi[{{ $lokKey }}][{{ $kompKey }}_ket]"
                                   class="form-control form-control-sm"
                                   placeholder="Keterangan (opsional)..."
                                   value="{{ old("lokasi.{$lokKey}.{$kompKey}_ket") }}">
                        </div>
                        @endforeach

                        {{-- Upload Foto Bukti per Lokasi --}}
                        <div class="mt-2">
                            <label class="form-label small fw-medium">
                                <i class="ti ti-camera me-1"></i>Foto Bukti
                                <span class="text-muted">(opsional, maks. 3 foto)</span>
                            </label>
                            <input type="file"
                                   name="foto_{{ $lokKey }}[]"
                                   id="foto-{{ $lokKey }}"
                                   class="form-control form-control-sm"
                                   accept="image/jpeg,image/png,image/jpg"
                                   multiple>
                            <div class="form-text text-muted">Format: JPG/PNG. Maks. 5MB per foto.</div>
                            <div id="preview-{{ $lokKey }}" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                    </div>
                </div>
                @endforeach

                <button type="submit" class="btn btn-primary w-100 mb-4">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Laporan Pemeriksaan Hydrant
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Pemeriksaan
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('inspeksi-hydrant.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-flame-off" style="font-size:2.5rem;"></i></div>
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
                        <a href="{{ route('inspeksi-hydrant.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge {{ $log->lokasi_aman == 5 ? 'bg-success-lt text-success' : ($log->lokasi_aman >= 3 ? 'bg-warning-lt text-warning' : 'bg-danger-lt text-danger') }}">
                                    {{ $log->lokasi_aman }}/5 Aman
                                </span>
                            </div>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lokasiKeys = @json(array_keys($lokasi));

    lokasiKeys.forEach(function (lok) {
        const input   = document.getElementById('foto-' + lok);
        const preview = document.getElementById('preview-' + lok);

        if (!input) return;

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            const files = Array.from(this.files).slice(0, 3);

            if (this.files.length > 3) {
                this.value = '';
                alert('Maksimal 3 foto per lokasi.');
                return;
            }

            files.forEach(function (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });
});
</script>
@endpush
@endsection
