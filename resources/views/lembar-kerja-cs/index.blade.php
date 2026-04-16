@extends('layouts.app')

@section('title', 'Lembar Kerja CS')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Lembar Kerja Saya</h2>
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
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ── --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('lembar-kerja-cs.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Informasi Dasar --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Lembar Kerja</h3>
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
                            <label class="form-label required">Area Tugas</label>
                            <select name="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Area --</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->nama }}
                                </option>
                                @endforeach
                            </select>
                            @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">Shift Tugas</label>
                            <select name="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Shift --</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->nama }}
                                    ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                                </option>
                                @endforeach
                            </select>
                            @error('shift_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Kegiatan Periodik --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#1a56db;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-list-check me-2"></i>Kegiatan Periodik
                        </h3>
                        <div class="card-actions">
                            <span class="badge bg-white text-dark" id="count-periodik">0 dipilih</span>
                        </div>
                    </div>
                    <div class="card-body pb-2" id="rows-periodik"></div>
                    <div class="card-footer pt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btn-add-periodik">
                            <i class="ti ti-plus me-1"></i>Tambah Kegiatan Periodik
                        </button>
                        @error('kegiatan_periodik')
                        <div class="text-danger small mt-1"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="text-muted small mt-1">Minimal 3 kegiatan periodik harus diisi.</div>
                    </div>
                </div>

                {{-- Kegiatan Extra Job --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#0ca678;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-star me-2"></i>Kegiatan Extra Job
                        </h3>
                        <div class="card-actions">
                            <span class="badge bg-white text-dark" id="count-extra">0 dipilih</span>
                        </div>
                    </div>
                    <div class="card-body pb-2" id="rows-extra"></div>
                    <div class="card-footer pt-2">
                        <button type="button" class="btn btn-outline-success btn-sm w-100" id="btn-add-extra">
                            <i class="ti ti-plus me-1"></i>Tambah Kegiatan Extra Job
                        </button>
                        <div class="text-muted small mt-1">Opsional.</div>
                    </div>
                </div>

                {{-- Dokumentasi --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-camera me-1"></i>Dokumentasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="ti ti-alert-triangle me-1"></i>
                            Pastikan foto memiliki <strong>timestamp (tanggal &amp; jam)</strong> yang terlihat jelas.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Dokumentasi <span class="text-muted">(opsional, maks. 20 foto)</span></label>
                            <input type="file" name="foto_dokumentasi[]" id="foto-dokumentasi"
                                   class="form-control @error('foto_dokumentasi') is-invalid @enderror"
                                   accept="image/jpeg,image/jpg,image/png" multiple>
                            <div class="form-text">Format: JPG/PNG. Maks. 10MB per foto.</div>
                            @error('foto_dokumentasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="foto-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Foto</label>
                            <input type="text" name="deskripsi_foto"
                                   class="form-control @error('deskripsi_foto') is-invalid @enderror"
                                   placeholder="Contoh: Area IGD setelah dibersihkan"
                                   value="{{ old('deskripsi_foto') }}" maxlength="500">
                            @error('deskripsi_foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Catatan <span class="text-muted">(opsional)</span></label>
                            <textarea name="catatan"
                                      class="form-control @error('catatan') is-invalid @enderror"
                                      rows="3" placeholder="Keterangan tambahan..."
                                      maxlength="1000">{{ old('catatan') }}</textarea>
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-send me-1"></i>Simpan &amp; Kirim ke Koordinator
                        </button>
                    </div>
                </div>

                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-history me-1"></i>Riwayat</h3>
                        <div class="card-actions">
                            <form method="GET" class="d-flex gap-1 align-items-center">
                                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('M') }}
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
                            <div class="empty-icon"><i class="ti ti-clipboard-off" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada lembar kerja</p>
                        </div>
                    </div>
                    @else
                    <div class="card-body border-bottom py-2 text-center">
                        <span class="text-muted small">{{ $riwayat->count() }} lembar kerja bulan ini</span>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" style="max-height:75vh;">
                        @foreach($riwayat as $lk)
                        <a href="{{ route('lembar-kerja-cs.show', $lk) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $lk->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge bg-{{ $lk->status_color }}-lt text-{{ $lk->status_color }}">
                                    {{ $lk->status_label }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                {{ $lk->area->nama ?? '-' }} · {{ $lk->shift->nama ?? '-' }}
                                · {{ count($lk->kegiatan_periodik ?? []) }} periodik
                                @if(!empty($lk->kegiatan_extra_job))
                                · {{ count($lk->kegiatan_extra_job) }} extra
                                @endif
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
const optionsPeriodik = @json($periodik->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama]));
const optionsExtra    = @json($extraJob->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama]));

function buildRow(options, name) {
    let opts = '<option value="">-- Pilih Kegiatan --</option>';
    options.forEach(o => opts += `<option value="${o.id}">${o.nama}</option>`);
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-center kegiatan-row';
    row.innerHTML = `
        <div class="col">
            <select name="${name}" class="form-select form-select-sm" required>${opts}</select>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-ghost-danger btn-sm btn-hapus">
                <i class="ti ti-trash"></i>
            </button>
        </div>`;
    return row;
}

function syncCount() {
    document.getElementById('count-periodik').textContent =
        document.querySelectorAll('#rows-periodik .kegiatan-row').length + ' dipilih';
    document.getElementById('count-extra').textContent =
        document.querySelectorAll('#rows-extra .kegiatan-row').length + ' dipilih';
}

document.getElementById('btn-add-periodik').addEventListener('click', function () {
    document.getElementById('rows-periodik').appendChild(buildRow(optionsPeriodik, 'kegiatan_periodik[]'));
    syncCount();
});

document.getElementById('btn-add-extra').addEventListener('click', function () {
    document.getElementById('rows-extra').appendChild(buildRow(optionsExtra, 'kegiatan_extra_job[]'));
    syncCount();
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-hapus')) {
        e.target.closest('.kegiatan-row').remove();
        syncCount();
    }
});

// Foto preview
document.getElementById('foto-dokumentasi').addEventListener('change', function () {
    const preview = document.getElementById('foto-preview');
    preview.innerHTML = '';
    if (this.files.length > 20) { this.value = ''; alert('Maksimal 20 foto.'); return; }
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
@endsection
