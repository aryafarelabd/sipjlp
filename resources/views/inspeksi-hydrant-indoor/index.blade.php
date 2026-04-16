@extends('layouts.app')

@section('title', 'Pemeriksaan Hydrant Indoor')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Pemeriksaan Hydrant Indoor</h2>
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
                <form method="POST" action="{{ route('inspeksi-hydrant-indoor.store') }}" enctype="multipart/form-data">
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
                        <div class="mb-0">
                            <label class="form-label required">Lokasi Hydrant</label>
                            <select name="lokasi" class="form-select @error('lokasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach(\App\Models\InspeksiHydrantIndoor::LOKASI as $key => $label)
                                <option value="{{ $key }}" {{ old('lokasi') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Hydrant 1 & 2 --}}
                @foreach(['hydrant_1' => 'Hydrant 1', 'hydrant_2' => 'Hydrant 2'] as $hKey => $hLabel)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-flame me-2"></i>{{ $hLabel }}
                        </h3>
                    </div>
                    <div class="card-body">
                        @foreach(\App\Models\InspeksiHydrantIndoor::KOMPONEN as $kompKey => $kompConfig)
                        @php $isRequired = $kompConfig['required'] ?? true; @endphp
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <label class="form-label fw-medium {{ $isRequired ? 'required' : '' }}">{{ $kompConfig['label'] }}</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($kompConfig['options'] as $optVal => $optLabel)
                                <label class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input"
                                           name="{{ $hKey }}[{{ $kompKey }}]"
                                           value="{{ $optVal }}"
                                           {{ $isRequired ? 'required' : '' }}
                                           {{ old("{$hKey}.{$kompKey}") === $optVal ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ $optLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                            @if(isset($kompConfig['keterangan']) && $kompConfig['keterangan'])
                            <input type="text"
                                   name="{{ $hKey }}[{{ $kompKey }}_ket]"
                                   class="form-control form-control-sm"
                                   placeholder="Keterangan (opsional)..."
                                   value="{{ old("{$hKey}.{$kompKey}_ket") }}">
                            @endif
                        </div>
                        @endforeach

                        {{-- Keterangan umum --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Keterangan Umum (opsional)</label>
                            <textarea name="{{ $hKey }}[keterangan]"
                                      class="form-control form-control-sm"
                                      rows="2"
                                      placeholder="Uraian keterangan lain...">{{ old("{$hKey}.keterangan") }}</textarea>
                        </div>

                        {{-- Upload Foto Bukti --}}
                        <div class="mt-2">
                            <label class="form-label small fw-medium">
                                <i class="ti ti-camera me-1"></i>Foto Bukti
                                <span class="text-muted">(opsional, maks. 3 foto)</span>
                            </label>
                            <input type="file"
                                   name="foto_{{ $hKey }}[]"
                                   id="foto-{{ $hKey }}"
                                   class="form-control form-control-sm"
                                   accept="image/jpeg,image/png,image/jpg"
                                   multiple>
                            <div class="form-text text-muted">Format: JPG/PNG. Maks. 5MB per foto.</div>
                            <div id="preview-{{ $hKey }}" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="btn btn-primary w-100 mb-4">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Laporan Pemeriksaan Hydrant Indoor
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
                            <form method="GET" action="{{ route('inspeksi-hydrant-indoor.index') }}"
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
                        @php $amanCount = $log->hydrant_aman; @endphp
                        <a href="{{ route('inspeksi-hydrant-indoor.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge {{ $amanCount == 2 ? 'bg-success-lt text-success' : ($amanCount == 1 ? 'bg-warning-lt text-warning' : 'bg-danger-lt text-danger') }}">
                                    {{ $amanCount }}/2 Aman
                                </span>
                            </div>
                            <div class="text-muted small">
                                {{ \App\Models\InspeksiHydrantIndoor::LOKASI[$log->lokasi] ?? $log->lokasi }}
                                · {{ $log->shift->nama ?? '-' }}
                            </div>
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
    ['hydrant_1', 'hydrant_2'].forEach(function (h) {
        const input   = document.getElementById('foto-' + h);
        const preview = document.getElementById('preview-' + h);
        if (!input) return;
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            if (this.files.length > 3) {
                this.value = '';
                alert('Maksimal 3 foto per hydrant.');
                return;
            }
            Array.from(this.files).forEach(function (file) {
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
