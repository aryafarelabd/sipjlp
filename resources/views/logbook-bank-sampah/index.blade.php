@extends('layouts.app')

@section('title', 'Logbook Bank Sampah')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Logbook Bank Sampah</h2>
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

        {{-- Info --}}
        <div class="alert alert-info mb-3">
            <i class="ti ti-info-circle me-2"></i>
            <strong>Catatan:</strong> Penginputan dilakukan saat pihak pengepul datang mengambil sampah yang sudah dikumpulkan.
            Kosongkan jenis sampah yang tidak ada pada pengambilan ini.
        </div>

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ──────────────────────────────────── --}}
            <div class="col-lg-6">
                <form method="POST" action="{{ route('logbook-bank-sampah.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Info Dasar --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Pengambilan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label class="form-label required">Tanggal Pengambilan Sampah</label>
                            <input type="date" name="tanggal"
                                   class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Jenis Sampah --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-recycle me-2"></i>Timbulan Sampah
                            <small class="fw-normal ms-1 opacity-75">— Isi yang ada saja (kg)</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($jenis as $field => $label)
                            <div class="col-6">
                                <label class="form-label small mb-1">{{ $label }} (kg)</label>
                                <input type="number" name="{{ $field }}" step="0.01" min="0"
                                       class="form-control form-control-sm @error($field) is-invalid @enderror"
                                       value="{{ old($field) }}" placeholder="0.00">
                                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Foto & Catatan --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-camera me-1"></i>Dokumentasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">
                                Bukti Dokumentasi
                                <span class="text-muted small fw-normal">(maks. 5 foto)</span>
                            </label>
                            <input type="file" name="foto[]" accept="image/*" multiple
                                   class="form-control @error('foto') is-invalid @enderror @error('foto.*') is-invalid @enderror"
                                   id="inputFoto" required>
                            <div class="form-hint">Foto timbulan sampah sebelum dan/atau sesudah pengambilan.</div>
                            @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('foto.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="previewFoto" class="d-flex flex-wrap gap-1 mt-2"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">
                                Catatan
                                <span class="text-muted small fw-normal">(opsional)</span>
                            </label>
                            <textarea name="catatan" class="form-control" rows="2"
                                      maxlength="500" placeholder="Keterangan tambahan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Logbook Bank Sampah
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Bank Sampah
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('logbook-bank-sampah.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-recycle-off" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada entri</p>
                            <p class="empty-subtitle text-muted">Gunakan form di sebelah kiri untuk menambah logbook.</p>
                        </div>
                    </div>
                    @else
                    @php $totalBulan = $riwayat->sum('total_berat'); @endphp
                    <div class="card-body border-bottom py-2">
                        <div class="row g-2 text-center">
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Entri</div>
                                <div class="fw-bold">{{ $riwayat->count() }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Total Sampah</div>
                                <div class="fw-bold text-green">{{ number_format($totalBulan, 1) }} kg</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush overflow-auto" style="max-height:70vh;">
                        @foreach($riwayat as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge bg-green-lt text-green fw-medium">
                                    Total {{ number_format($log->total_berat, 1) }} kg
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($jenis as $field => $label)
                                    @if($log->$field > 0)
                                    <span class="badge bg-teal-lt text-teal">
                                        {{ $label }}: {{ number_format($log->$field, 1) }} kg
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                            @if($log->catatan)
                            <div class="text-muted small mb-1"><i class="ti ti-note me-1"></i>{{ $log->catatan }}</div>
                            @endif
                            @if($log->fotos->isNotEmpty())
                            <button type="button" class="btn btn-xs btn-ghost-primary"
                                    onclick="lihatFotos({{ $log->fotos->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Bukti Dokumentasi')">
                                <i class="ti ti-photo me-1"></i>Foto ({{ $log->fotos->count() }})
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal foto --}}
<div class="modal modal-blur fade" id="fotoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoModalTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2" id="fotoModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('inputFoto').addEventListener('change', function () {
    const preview = document.getElementById('previewFoto');
    preview.innerHTML = '';
    Array.from(this.files).slice(0, 5).forEach(function (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:64px;height:64px;object-fit:cover;border-radius:4px;border:1px solid #ddd;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

function lihatFotos(urls, judul) {
    document.getElementById('fotoModalTitle').textContent = judul;
    const body = document.getElementById('fotoModalBody');
    body.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'd-flex flex-wrap gap-2 justify-content-center';
    urls.forEach(function (url) {
        const a = document.createElement('a');
        a.href = url; a.target = '_blank';
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'max-height:300px;max-width:100%;object-fit:contain;border-radius:6px;cursor:pointer;';
        a.appendChild(img);
        wrap.appendChild(a);
    });
    body.appendChild(wrap);
    new bootstrap.Modal(document.getElementById('fotoModal')).show();
}
</script>
@endpush
