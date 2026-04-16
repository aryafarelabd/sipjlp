@extends('layouts.app')

@section('title', 'Logbook Limbah B3')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Logbook Limbah B3</h2>
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
            <div class="col-lg-6">
                <form method="POST" action="{{ route('logbook-b3.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Info Dasar --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Umum</h3>
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

                {{-- Plastik Kuning --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-trash me-2"></i>Plastik Kuning
                            <small class="fw-normal ms-1 opacity-75">— Limbah Padat Infeksius</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Jika ditimbang dengan sulo, kurangi 10,5 kg (berat sulo kosong). Kosongkan jika tidak ada limbah.
                        </p>
                        <div class="row g-2">
                            @php
                                $pkLokasi = [
                                    'pk_lt1'      => 'Lantai 1',
                                    'pk_igd'      => 'IGD',
                                    'pk_lt2'      => 'Lantai 2',
                                    'pk_ok'       => 'OK',
                                    'pk_lt3'      => 'Lantai 3',
                                    'pk_lt4'      => 'Lantai 4',
                                    'pk_utilitas' => 'Utilitas',
                                    'pk_taman'    => 'Taman / Halaman',
                                ];
                            @endphp
                            @foreach($pkLokasi as $field => $label)
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

                {{-- Safety Box --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-box me-2"></i>Safety Box
                            <small class="fw-normal ms-1 opacity-75">— Limbah Tajam Infeksius</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Kosongkan jika tidak ada limbah safety box.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small">Dari ruangan / lantai mana?</label>
                            <input type="text" name="safety_box_asal"
                                   class="form-control @error('safety_box_asal') is-invalid @enderror"
                                   value="{{ old('safety_box_asal') }}"
                                   placeholder="Contoh: Lantai 2, Poli Bedah">
                            @error('safety_box_asal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Jumlah timbangan (kg)</label>
                            <input type="number" name="safety_box_kg" step="0.01" min="0"
                                   class="form-control @error('safety_box_kg') is-invalid @enderror"
                                   value="{{ old('safety_box_kg') }}" placeholder="0.00">
                            @error('safety_box_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Limbah Cair Infeksius --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-droplet me-2"></i>Limbah Cair
                            <small class="fw-normal ms-1 opacity-75">— Infeksius</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Biasanya berasal dari laboratorium. Kosongkan jika tidak ada.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small">Dari ruangan / lantai mana?</label>
                            <input type="text" name="cair_asal"
                                   class="form-control @error('cair_asal') is-invalid @enderror"
                                   value="{{ old('cair_asal') }}"
                                   placeholder="Contoh: Laboratorium Lt.1">
                            @error('cair_asal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Jumlah timbangan (kg)</label>
                            <input type="number" name="cair_kg" step="0.01" min="0"
                                   class="form-control @error('cair_kg') is-invalid @enderror"
                                   value="{{ old('cair_kg') }}" placeholder="0.00">
                            @error('cair_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Hepafilter --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-filter me-2"></i>Hepafilter
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Kosongkan jika tidak ada limbah hepafilter.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small">Dari ruangan / lantai mana?</label>
                            <input type="text" name="hepafilter_asal"
                                   class="form-control @error('hepafilter_asal') is-invalid @enderror"
                                   value="{{ old('hepafilter_asal') }}"
                                   placeholder="Contoh: ICU Lt.3">
                            @error('hepafilter_asal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Jumlah timbangan (kg)</label>
                            <input type="number" name="hepafilter_kg" step="0.01" min="0"
                                   class="form-control @error('hepafilter_kg') is-invalid @enderror"
                                   value="{{ old('hepafilter_kg') }}" placeholder="0.00">
                            @error('hepafilter_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Non Infeksius --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fd7e14;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-recycle me-2"></i>Non Infeksius
                            <small class="fw-normal ms-1 opacity-75">— Limbah B3</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Jenis: Lampu TL, Chemical kadaluarsa, Obat kadaluarsa, Oli, Aki.
                            Kosongkan jika tidak ada.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small">Jenis limbah B3 non infeksius</label>
                            <input type="text" name="non_infeksius_jenis"
                                   class="form-control @error('non_infeksius_jenis') is-invalid @enderror"
                                   value="{{ old('non_infeksius_jenis') }}"
                                   placeholder="Contoh: Lampu TL, Obat kadaluarsa">
                            @error('non_infeksius_jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Jumlah timbangan (kg)</label>
                            <input type="number" name="non_infeksius_kg" step="0.01" min="0"
                                   class="form-control @error('non_infeksius_kg') is-invalid @enderror"
                                   value="{{ old('non_infeksius_kg') }}" placeholder="0.00">
                            @error('non_infeksius_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <label class="form-label">
                                Foto Pemakaian APD
                                <span class="text-muted small fw-normal">(maks. 5 foto)</span>
                            </label>
                            <input type="file" name="foto_apd[]" accept="image/*" multiple
                                   class="form-control @error('foto_apd') is-invalid @enderror @error('foto_apd.*') is-invalid @enderror"
                                   id="inputFotoApd">
                            <div class="form-hint">Sarung tangan, masker, apron — wajib ber-timestamp.</div>
                            @error('foto_apd')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('foto_apd.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="previewApd" class="d-flex flex-wrap gap-1 mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Foto Timbangan
                                <span class="text-muted small fw-normal">(maks. 5 foto)</span>
                            </label>
                            <input type="file" name="foto_timbangan[]" accept="image/*" multiple
                                   class="form-control @error('foto_timbangan') is-invalid @enderror @error('foto_timbangan.*') is-invalid @enderror"
                                   id="inputFotoTimbangan">
                            <div class="form-hint">Foto jarum timbangan — wajib ber-timestamp.</div>
                            @error('foto_timbangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('foto_timbangan.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="previewTimbangan" class="d-flex flex-wrap gap-1 mt-2"></div>
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
                    <i class="ti ti-device-floppy me-1"></i>Simpan Logbook B3
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Logbook B3 Saya
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('logbook-b3.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-clipboard-list" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada entri</p>
                            <p class="empty-subtitle text-muted">Gunakan form di sebelah kiri untuk menambah logbook.</p>
                        </div>
                    </div>
                    @else
                    @php
                        $totalPk          = $riwayat->sum('total_pk');
                        $totalSafetyBox   = $riwayat->sum('safety_box_kg');
                        $totalCair        = $riwayat->sum('cair_kg');
                        $totalHepafilter  = $riwayat->sum('hepafilter_kg');
                        $totalNonInfeksius = $riwayat->sum('non_infeksius_kg');
                    @endphp
                    <div class="card-body border-bottom py-2">
                        <div class="row g-2 text-center">
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Entri</div>
                                <div class="fw-bold">{{ $riwayat->count() }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Plastik Kuning</div>
                                <div class="fw-bold text-warning">{{ number_format($totalPk, 1) }} kg</div>
                            </div>
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Safety Box</div>
                                <div class="fw-bold text-orange">{{ number_format($totalSafetyBox, 1) }} kg</div>
                            </div>
                            <div class="col">
                                <div class="text-muted" style="font-size:11px;">Non Infeksius</div>
                                <div class="fw-bold text-secondary">{{ number_format($totalNonInfeksius, 1) }} kg</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush overflow-auto" style="max-height:70vh;">
                        @foreach($riwayat as $log)
                        <div class="list-group-item">
                            <div class="row align-items-start g-2">
                                <div class="col">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                            <span class="text-muted small ms-2">{{ $log->shift->nama ?? '-' }}</span>
                                        </div>
                                        <span class="badge bg-yellow-lt text-yellow fw-medium">
                                            Total {{ number_format($log->total_berat, 1) }} kg
                                        </span>
                                    </div>
                                    <div class="text-muted small">{{ $log->area->nama ?? '-' }}</div>

                                    {{-- Detail per jenis --}}
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @if($log->total_pk > 0)
                                        <span class="badge bg-warning-lt text-warning">
                                            PK: {{ number_format($log->total_pk, 1) }} kg
                                        </span>
                                        @endif
                                        @if($log->safety_box_kg)
                                        <span class="badge bg-orange-lt text-orange">
                                            Safety Box: {{ number_format($log->safety_box_kg, 1) }} kg
                                        </span>
                                        @endif
                                        @if($log->cair_kg)
                                        <span class="badge bg-blue-lt text-blue">
                                            Cair: {{ number_format($log->cair_kg, 1) }} kg
                                        </span>
                                        @endif
                                        @if($log->hepafilter_kg)
                                        <span class="badge bg-purple-lt text-purple">
                                            Hepafilter: {{ number_format($log->hepafilter_kg, 1) }} kg
                                        </span>
                                        @endif
                                        @if($log->non_infeksius_kg)
                                        <span class="badge bg-secondary-lt text-secondary">
                                            Non-Inf: {{ number_format($log->non_infeksius_kg, 1) }} kg
                                            @if($log->non_infeksius_jenis) ({{ $log->non_infeksius_jenis }}) @endif
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Foto --}}
                                    <div class="d-flex gap-2 mt-2">
                                        @if($log->fotosApd->isNotEmpty())
                                        <button type="button" class="btn btn-xs btn-ghost-primary"
                                                onclick="lihatFotos({{ $log->fotosApd->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto APD')">
                                            <i class="ti ti-shield-check me-1"></i>APD ({{ $log->fotosApd->count() }})
                                        </button>
                                        @endif
                                        @if($log->fotosTimbangan->isNotEmpty())
                                        <button type="button" class="btn btn-xs btn-ghost-success"
                                                onclick="lihatFotos({{ $log->fotosTimbangan->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto Timbangan')">
                                            <i class="ti ti-scale me-1"></i>Timbangan ({{ $log->fotosTimbangan->count() }})
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
function setupPreview(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;
    input.addEventListener('change', function () {
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
}
setupPreview('inputFotoApd', 'previewApd');
setupPreview('inputFotoTimbangan', 'previewTimbangan');

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
