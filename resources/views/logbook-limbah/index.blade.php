@extends('layouts.app')

@section('title', 'Logbook Limbah')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Logbook Limbah</h2>
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
            <ul class="mb-0">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form Input ─────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-plus me-1"></i>Tambah Entri Logbook</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('logbook-limbah.store') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- Tanggal --}}
                            <div class="mb-3">
                                <label class="form-label required">Tanggal</label>
                                <input type="date" name="tanggal"
                                       class="form-control @error('tanggal') is-invalid @enderror"
                                       value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Area --}}
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

                            {{-- Shift --}}
                            <div class="mb-3">
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

                            {{-- Berat Limbah Domestik --}}
                            <div class="mb-3">
                                <label class="form-label required">Berat Limbah Domestik (kg)</label>
                                <input type="number" name="berat_domestik" step="0.01" min="0"
                                       class="form-control @error('berat_domestik') is-invalid @enderror"
                                       value="{{ old('berat_domestik') }}"
                                       placeholder="0.00" required>
                                <div class="form-hint">Jika ditimbang dengan sulo, kurangi 10,5 kg (berat sulo kosong).</div>
                                @error('berat_domestik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Berat Kompos --}}
                            <div class="mb-3">
                                <label class="form-label required">Berat Limbah Organik / Kompos (kg)</label>
                                <input type="number" name="berat_kompos" step="0.01" min="0"
                                       class="form-control @error('berat_kompos') is-invalid @enderror"
                                       value="{{ old('berat_kompos') }}"
                                       placeholder="0.00" required>
                                <div class="form-hint">Sampah dapur (buah, sayur, sisa masak) sebelum masuk tong komposter.</div>
                                @error('berat_kompos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Foto APD --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    Foto Pemakaian APD
                                    <span class="text-muted small fw-normal">(maks. 5 foto)</span>
                                </label>
                                <input type="file" name="foto_apd[]" accept="image/*"
                                       class="form-control @error('foto_apd') is-invalid @enderror @error('foto_apd.*') is-invalid @enderror"
                                       multiple>
                                <div class="form-hint">Sarung tangan, masker, apron — wajib ber-timestamp.</div>
                                @error('foto_apd')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('foto_apd.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div id="previewApd" class="d-flex flex-wrap gap-1 mt-2"></div>
                            </div>

                            {{-- Foto Timbangan --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    Foto Timbangan
                                    <span class="text-muted small fw-normal">(maks. 5 foto)</span>
                                </label>
                                <input type="file" name="foto_timbangan[]" accept="image/*"
                                       class="form-control @error('foto_timbangan') is-invalid @enderror @error('foto_timbangan.*') is-invalid @enderror"
                                       multiple>
                                <div class="form-hint">Foto jarum timbangan — wajib ber-timestamp.</div>
                                @error('foto_timbangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('foto_timbangan.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div id="previewTimbangan" class="d-flex flex-wrap gap-1 mt-2"></div>
                            </div>

                            {{-- Catatan --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    Catatan
                                    <span class="text-muted small fw-normal">(opsional)</span>
                                </label>
                                <textarea name="catatan" class="form-control" rows="2"
                                          maxlength="500"
                                          placeholder="Keterangan tambahan...">{{ old('catatan') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Logbook
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Logbook Saya
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('logbook-limbah.index') }}"
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
                        $totalDomestik = $riwayat->sum('berat_domestik');
                        $totalKompos   = $riwayat->sum('berat_kompos');
                    @endphp
                    <div class="card-body border-bottom py-2">
                        <div class="row g-3 text-center">
                            <div class="col">
                                <div class="text-muted small">Total Entri</div>
                                <div class="fw-bold">{{ $riwayat->count() }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small">Total Domestik</div>
                                <div class="fw-bold text-blue">{{ number_format($totalDomestik, 1) }} kg</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small">Total Kompos</div>
                                <div class="fw-bold text-green">{{ number_format($totalKompos, 1) }} kg</div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Area / Shift</th>
                                    <th class="text-end">Domestik</th>
                                    <th class="text-end">Kompos</th>
                                    <th style="width:70px;">Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayat as $log)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</div>
                                        <small class="text-muted">{{ $log->tanggal->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $log->area->nama ?? '-' }}</div>
                                        <span class="badge bg-secondary-lt text-secondary small">
                                            {{ $log->shift->nama ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium text-blue">{{ number_format($log->berat_domestik, 1) }}</span>
                                        <small class="text-muted"> kg</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium text-green">{{ number_format($log->berat_kompos, 1) }}</span>
                                        <small class="text-muted"> kg</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($log->fotosApd->isNotEmpty())
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-ghost-primary"
                                                    title="Foto APD ({{ $log->fotosApd->count() }})"
                                                    onclick="lihatFotos({{ $log->fotosApd->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto APD')">
                                                <i class="ti ti-shield-check"></i>
                                            </button>
                                            @endif
                                            @if($log->fotosTimbangan->isNotEmpty())
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-ghost-success"
                                                    title="Foto Timbangan ({{ $log->fotosTimbangan->count() }})"
                                                    onclick="lihatFotos({{ $log->fotosTimbangan->pluck('path')->map(fn($p) => asset('storage/'.$p))->toJson() }}, 'Foto Timbangan')">
                                                <i class="ti ti-scale"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if($log->catatan)
                                <tr>
                                    <td colspan="5" class="pt-0">
                                        <small class="text-muted"><i class="ti ti-note me-1"></i>{{ $log->catatan }}</small>
                                    </td>
                                </tr>
                                @endif
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

{{-- Modal lihat foto --}}
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
function setupPreview(inputName, previewId) {
    const input   = document.querySelector('input[name="' + inputName + '"]');
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
setupPreview('foto_apd[]', 'previewApd');
setupPreview('foto_timbangan[]', 'previewTimbangan');

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
