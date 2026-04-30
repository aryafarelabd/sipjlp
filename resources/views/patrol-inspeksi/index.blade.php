@extends('layouts.app')

@section('title', 'Laporan Security Patrol')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Inspeksi Area Rumah Sakit (Security Patrol)</h2>
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
                <form method="POST" action="{{ route('patrol-inspeksi.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Header --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Informasi Patroli</h3>
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
                            <label class="form-label required">Fokus Area Inspeksi</label>
                            <select name="area" class="form-select @error('area') is-invalid @enderror" required>
                                <option value="">-- Pilih Area --</option>
                                @foreach($area as $val => $label)
                                <option value="{{ $val }}" {{ old('area') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Seksi Inspeksi --}}
                @foreach($seksi as $key => $config)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-clipboard-check me-2"></i>{{ $config['label'] }}
                            @if($config['foto_required'])
                            <span class="badge bg-red-lt text-red ms-2 fw-normal" style="font-size:11px;">Foto Wajib</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th>Item Inspeksi</th>
                                        <th class="text-center" width="80">Ya</th>
                                        <th class="text-center" width="80">Tidak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($config['items'] as $itemKey => $itemLabel)
                                    <tr>
                                        <td class="small">{{ $itemLabel }}</td>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="seksi[{{ $key }}][{{ $itemKey }}]"
                                                   value="1"
                                                   {{ old("seksi.{$key}.{$itemKey}") === '1' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="seksi[{{ $key }}][{{ $itemKey }}]"
                                                   value="0"
                                                   {{ old("seksi.{$key}.{$itemKey}") === '0' ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Foto per seksi --}}
                        <div>
                            <label class="form-label small {{ $config['foto_required'] ? 'required' : '' }}">
                                Foto Dokumentasi
                                <span class="text-muted fw-normal">(maks. 1 foto{{ $config['foto_required'] ? '' : ', opsional' }})</span>
                            </label>
                            <input type="file" name="foto[{{ $key }}][]" accept="image/*"
                                   class="form-control form-control-sm @error('foto.'.$key) is-invalid @enderror"
                                   id="foto_{{ $key }}"
                                   {{ $config['foto_required'] ? 'required' : '' }}>
                            <div class="form-hint small">Foto jika terdapat temuan ketidaksesuaian atau kondisi membahayakan.</div>
                            @error('foto.'.$key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="preview_{{ $key }}" class="mt-1"></div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Pembatasan Akses Malam --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-lock me-2"></i>Pembatasan Akses Malam (Pukul 21.00 WIB)
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Apakah sudah dipastikan seluruh pintu masuk dalam kondisi terkunci?</p>
                        <div class="table-responsive">
                            <table class="table table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th>Pintu</th>
                                        <th class="text-center" width="80">Sudah</th>
                                        <th class="text-center" width="80">Belum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pintuAkses as $pKey => $pLabel)
                                    <tr>
                                        <td class="small">{{ $pLabel }}</td>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="pintu_akses[{{ $pKey }}]" value="1"
                                                   {{ old("pintu_akses.{$pKey}") === '1' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="pintu_akses[{{ $pKey }}]" value="0"
                                                   {{ old("pintu_akses.{$pKey}") === '0' ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Jika terdapat pintu belum ditutup, berikan alasannya</label>
                            <textarea name="alasan_pintu" class="form-control form-control-sm" rows="2"
                                      maxlength="1000" placeholder="Alasan...">{{ old('alasan_pintu') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label small">Kirimkan Dokumen Foto Penemuan Anda</label>
                            <input type="file" name="foto_temuan" accept="image/*"
                                   class="form-control form-control-sm" id="foto_temuan">
                            <div id="preview_temuan" class="mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Rekomendasi --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-bulb me-2"></i>Rekomendasi Perbaikan
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Sebutkan item mana saja yang perlu perbaikan secara detail dan jelas agar ditindaklanjuti oleh K3 Rumah Sakit.
                        </p>
                        <textarea name="rekomendasi" rows="5"
                                  class="form-control @error('rekomendasi') is-invalid @enderror"
                                  placeholder="Contoh: Tangga darurat Lantai 2 — keramik retak di anak tangga ke-3, perlu segera diperbaiki sebelum menimbulkan risiko terpeleset..."
                                  required>{{ old('rekomendasi') }}</textarea>
                        @error('rekomendasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Laporan Security Patrol
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Laporan Patrol
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('patrol-inspeksi.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-shield-off" style="font-size:2.5rem;"></i></div>
                            <p class="empty-title">Belum ada laporan</p>
                            <p class="empty-subtitle text-muted">Gunakan form di sebelah kiri untuk menambah laporan patrol.</p>
                        </div>
                    </div>
                    @else
                    <div class="card-body border-bottom py-2 text-center">
                        <span class="text-muted small">{{ $riwayat->count() }} laporan bulan ini</span>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" style="max-height:75vh;">
                        @foreach($riwayat as $log)
                        <a href="{{ route('patrol-inspeksi.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge bg-purple-lt text-purple">{{ $log->persentase }}% OK</span>
                            </div>
                            <div class="text-muted small">
                                {{ \App\Models\PatrolInspeksi::AREA[$log->area] ?? $log->area }}
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
@endsection

@push('scripts')
<script>
// Preview foto per seksi
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const previewId = 'preview_' + this.id.replace('foto_', '');
        const preview = document.getElementById(previewId);
        if (!preview) return;
        preview.innerHTML = '';
        Array.from(this.files).slice(0, 1).forEach(function(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>
@endpush
