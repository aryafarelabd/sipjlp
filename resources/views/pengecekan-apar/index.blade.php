@extends('layouts.app')

@section('title', 'Pengecekan APAR & APAB')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Pengecekan APAR &amp; APAB</h2>
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

            {{-- ── Kolom Kiri: Form ──────────────────────────────── --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('pengecekan-apar.store') }}" id="form-apar" enctype="multipart/form-data">
                @csrf

                {{-- Header --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Pengecekan</h3>
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
                            <label class="form-label required">Lokasi APAR &amp; APAB</label>
                            <select name="lokasi" id="select-lokasi"
                                    class="form-select @error('lokasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach(\App\Models\PengecekanApar::LOKASI as $key => $label)
                                <option value="{{ $key }}" {{ old('lokasi') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Unit Checklist per Lokasi --}}
                @foreach(\App\Models\PengecekanApar::UNITS_PER_LOKASI as $lokKey => $unitDefs)
                <div class="card mb-3 unit-section d-none" data-lokasi="{{ $lokKey }}">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-fire-extinguisher me-2"></i>
                            Nomor APAR &amp; Nama Ruangan — {{ \App\Models\PengecekanApar::LOKASI[$lokKey] }}
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter mb-0">
                            <thead>
                                <tr>
                                    <th>Nomor &amp; Nama Ruangan</th>
                                    <th width="80" class="text-center">Baik</th>
                                    <th width="80" class="text-center">Buruk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unitDefs as $unitKey => $unitLabel)
                                @php $oldVal = old("units.{$unitKey}"); @endphp
                                <tr>
                                    <td class="small fw-medium">{{ $unitLabel }}</td>
                                    <td class="text-center">
                                        <input type="radio"
                                               class="form-check-input"
                                               name="units[{{ $unitKey }}]"
                                               value="baik"
                                               {{ $oldVal === 'baik' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio"
                                               class="form-check-input"
                                               name="units[{{ $unitKey }}]"
                                               value="buruk"
                                               {{ $oldVal === 'buruk' ? 'checked' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <label class="form-label small">Keterangan jika ada yang Buruk (opsional)</label>
                        <textarea name="keterangan_buruk" class="form-control form-control-sm" rows="2"
                                  placeholder="Uraikan kondisi unit yang buruk...">{{ old('keterangan_buruk') }}</textarea>
                    </div>
                </div>
                @endforeach

                {{-- Rincian Pemeriksaan --}}
                <div class="card mb-3" id="card-rincian" style="display:none;">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-clipboard-list me-2"></i>Rincian Pemeriksaan
                        </h3>
                    </div>
                    <div class="card-body">

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-medium">Berat (kg)</label>
                                <input type="text" name="berat"
                                       class="form-control form-control-sm"
                                       placeholder="Contoh: 6 kg"
                                       value="{{ old('berat') }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-medium">Tekanan (M/H)</label>
                                <input type="text" name="tekanan"
                                       class="form-control form-control-sm"
                                       placeholder="M = Aman / H = Tidak Aman"
                                       value="{{ old('tekanan') }}">
                            </div>
                        </div>

                        {{-- Kondisi --}}
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="form-label fw-medium required small">Kondisi APAR &amp; APAB</label>
                            <div class="d-flex gap-3 mb-2">
                                @foreach(['baik' => 'Baik', 'buruk' => 'Buruk'] as $v => $l)
                                <label class="form-check">
                                    <input type="radio" class="form-check-input" name="kondisi"
                                           value="{{ $v }}" required
                                           {{ old('kondisi') === $v ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ $l }}</span>
                                </label>
                                @endforeach
                            </div>
                            <input type="text" name="kondisi_ket"
                                   class="form-control form-control-sm"
                                   placeholder="Keterangan jika kondisi buruk..."
                                   value="{{ old('kondisi_ket') }}">
                        </div>

                        {{-- Checklist komponen --}}
                        @php
                        $komponen = [
                            'pin_segel'    => ['label' => 'PIN Segel', 'options' => ['ada' => 'Ada', 'tidak' => 'Tidak']],
                            'handle'       => ['label' => 'Handle', 'options' => ['baik' => 'Baik / Berfungsi', 'buruk' => 'Buruk / Tidak Berfungsi']],
                            'petunjuk'     => ['label' => 'Petunjuk Penggunaan APAR & APAB', 'options' => ['ada' => 'Ada', 'tidak' => 'Tidak']],
                            'segitiga_api' => ['label' => 'Tanda Segitiga Api', 'options' => ['ada' => 'Ada', 'tidak' => 'Tidak']],
                        ];
                        @endphp

                        @foreach($komponen as $field => $cfg)
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <label class="form-label fw-medium required small">{{ $cfg['label'] }}</label>
                            <div class="d-flex gap-3">
                                @foreach($cfg['options'] as $v => $l)
                                <label class="form-check">
                                    <input type="radio" class="form-check-input"
                                           name="{{ $field }}" value="{{ $v }}" required
                                           {{ old($field) === $v ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ $l }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        {{-- Masa Berlaku --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium required small">Masa Berlaku APAR &amp; APAB</label>
                            <input type="date" name="masa_berlaku"
                                   class="form-control @error('masa_berlaku') is-invalid @enderror"
                                   value="{{ old('masa_berlaku') }}" required>
                            @error('masa_berlaku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Keterangan Lain --}}
                        <div class="mb-3">
                            <label class="form-label small">Keterangan Lain (opsional)</label>
                            <textarea name="keterangan_lain" class="form-control form-control-sm" rows="2"
                                      placeholder="Keterangan tambahan...">{{ old('keterangan_lain') }}</textarea>
                        </div>

                        {{-- Upload Foto Bukti --}}
                        <div class="mb-0">
                            <label class="form-label small fw-medium">
                                <i class="ti ti-camera me-1"></i>Foto Bukti Pemeriksaan
                                <span class="text-muted">(opsional, maks. 3 foto)</span>
                            </label>
                            <input type="file"
                                   name="foto_bukti[]"
                                   id="input-foto-bukti"
                                   class="form-control form-control-sm @error('foto_bukti') is-invalid @enderror @error('foto_bukti.*') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/jpg"
                                   multiple>
                            <div class="form-text text-muted">Format: JPG/PNG. Maks. 5MB per foto. Pilih hingga 3 foto sekaligus.</div>
                            @error('foto_bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('foto_bukti.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            {{-- Preview --}}
                            <div id="preview-foto" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4" id="btn-submit" style="display:none;">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Laporan Pengecekan APAR &amp; APAB
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Pengecekan
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('pengecekan-apar.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-fire-extinguisher" style="font-size:2.5rem;"></i></div>
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
                        @php $buruk = $log->jumlah_buruk; @endphp
                        <a href="{{ route('pengecekan-apar.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge {{ $buruk == 0 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                    {{ $buruk == 0 ? 'Semua Baik' : $buruk . ' Buruk' }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                {{ \App\Models\PengecekanApar::LOKASI[$log->lokasi] ?? $log->lokasi }}
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
    const selectLokasi = document.getElementById('select-lokasi');
    const allSections  = document.querySelectorAll('.unit-section');
    const cardRincian  = document.getElementById('card-rincian');
    const btnSubmit    = document.getElementById('btn-submit');

    function updateView(val) {
        allSections.forEach(s => {
            if (s.dataset.lokasi === val) {
                s.classList.remove('d-none');
            } else {
                s.classList.add('d-none');
            }
        });
        if (val) {
            cardRincian.style.display = '';
            btnSubmit.style.display   = '';
        } else {
            cardRincian.style.display = 'none';
            btnSubmit.style.display   = 'none';
        }
    }

    // Init from old() value (after validation fail)
    updateView(selectLokasi.value);

    selectLokasi.addEventListener('change', function () {
        updateView(this.value);
    });

    // Preview foto bukti
    const inputFoto   = document.getElementById('input-foto-bukti');
    const previewFoto = document.getElementById('preview-foto');

    if (inputFoto) {
        inputFoto.addEventListener('change', function () {
            previewFoto.innerHTML = '';
            const files = Array.from(this.files).slice(0, 3);

            if (files.length > 3) {
                this.value = '';
                alert('Maksimal 3 foto yang diperbolehkan.');
                return;
            }

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
                    previewFoto.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});
</script>
@endpush
@endsection
