@extends('layouts.app')

@section('title', 'Laporan Kecelakaan Kerja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">K3</div>
                <h2 class="page-title">Laporan Kecelakaan Kerja, Insiden &amp; Ketidaksesuaian</h2>
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

        <div class="alert alert-warning mb-3">
            <div class="d-flex gap-2">
                <i class="ti ti-alert-triangle flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Petunjuk Pengisian:</strong>
                    <ol class="mb-0 mt-1 small">
                        <li>Isi laporan ini segera pasca kejadian, baik oleh korban langsung maupun saksi.</li>
                        <li>Setelah mengisi form ini, <strong>print dan isi formulir hard copy</strong>, serahkan ke tim K3 (Maryanto), lalu upload filenya di bagian bawah form.</li>
                        <li>Melaporkan adalah upaya perbaikan — bukan mencari siapa yang bersalah.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row g-3">

            {{-- ── Kolom Kiri: Form ──────────────────────────────── --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('laporan-kecelakaan.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Identitas Pelapor --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-user me-1"></i>Identitas Pelapor</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Pelapor</label>
                            <input type="text" name="nama_pelapor"
                                   class="form-control @error('nama_pelapor') is-invalid @enderror"
                                   value="{{ old('nama_pelapor', auth()->user()->name) }}" required>
                            @error('nama_pelapor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Unit / Bagian</label>
                            <input type="text" name="unit_bagian"
                                   class="form-control @error('unit_bagian') is-invalid @enderror"
                                   value="{{ old('unit_bagian') }}"
                                   placeholder="Contoh: Security, Cleaning Service, Keperawatan..." required>
                            @error('unit_bagian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label required">Tanggal Kejadian</label>
                                <input type="date" name="tanggal"
                                       class="form-control @error('tanggal') is-invalid @enderror"
                                       value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label required">Waktu Kejadian</label>
                                <input type="time" name="waktu"
                                       class="form-control @error('waktu') is-invalid @enderror"
                                       value="{{ old('waktu') }}" required>
                                @error('waktu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informasi Kecelakaan --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-map-pin me-1"></i>Informasi Kecelakaan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Tempat Kejadian</label>
                            <input type="text" name="tempat"
                                   class="form-control @error('tempat') is-invalid @enderror"
                                   value="{{ old('tempat') }}"
                                   placeholder="Contoh: Ruang IGD Lantai 1" required>
                            @error('tempat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">Saksi-Saksi <small class="text-muted">(minimal 2 orang internal RS)</small></label>
                            <textarea name="saksi"
                                      class="form-control @error('saksi') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Sebutkan nama lengkap saksi, minimal 2 orang dari internal rumah sakit..." required>{{ old('saksi') }}</textarea>
                            @error('saksi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Data Korban --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-users me-1"></i>Data Korban</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label required">Jumlah Korban Laki-laki</label>
                                <input type="number" name="jumlah_laki" min="0" max="10"
                                       class="form-control @error('jumlah_laki') is-invalid @enderror"
                                       value="{{ old('jumlah_laki', 0) }}" required>
                                @error('jumlah_laki')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label required">Jumlah Korban Perempuan</label>
                                <input type="number" name="jumlah_perempuan" min="0" max="10"
                                       class="form-control @error('jumlah_perempuan') is-invalid @enderror"
                                       value="{{ old('jumlah_perempuan', 0) }}" required>
                                @error('jumlah_perempuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Nama Korban</label>
                            <textarea name="nama_korban"
                                      class="form-control @error('nama_korban') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Tulis nama lengkap semua korban..." required>{{ old('nama_korban') }}</textarea>
                            @error('nama_korban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Umur Korban</label>
                            <input type="text" name="umur_korban"
                                   class="form-control @error('umur_korban') is-invalid @enderror"
                                   value="{{ old('umur_korban') }}"
                                   placeholder="Contoh: 35 tahun / 8 bulan" required>
                            @error('umur_korban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Akibat Kecelakaan</label>
                            <div class="row g-3">
                                <div class="col-4">
                                    <label class="form-label small text-danger required">Mati</label>
                                    <input type="number" name="akibat_mati" min="0" max="10"
                                           class="form-control form-control-sm @error('akibat_mati') is-invalid @enderror"
                                           value="{{ old('akibat_mati', 0) }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-warning required">Luka Berat</label>
                                    <input type="number" name="akibat_luka_berat" min="0" max="10"
                                           class="form-control form-control-sm @error('akibat_luka_berat') is-invalid @enderror"
                                           value="{{ old('akibat_luka_berat', 0) }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-info required">Luka Ringan</label>
                                    <input type="number" name="akibat_luka_ringan" min="0" max="10"
                                           class="form-control form-control-sm @error('akibat_luka_ringan') is-invalid @enderror"
                                           value="{{ old('akibat_luka_ringan', 0) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label required">Keterangan Cedera / Bagian Tubuh yang Cidera</label>
                            <textarea name="keterangan_cedera"
                                      class="form-control @error('keterangan_cedera') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Jelaskan bagian tubuh yang cidera dan deskripsi cederanya..." required>{{ old('keterangan_cedera') }}</textarea>
                            @error('keterangan_cedera')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Fakta --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-search me-1"></i>Fakta yang Didapat</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Kondisi yang Berbahaya</label>
                            <textarea name="kondisi_berbahaya"
                                      class="form-control @error('kondisi_berbahaya') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Uraikan kondisi lingkungan/situasi yang berbahaya saat kejadian..." required>{{ old('kondisi_berbahaya') }}</textarea>
                            @error('kondisi_berbahaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tindakan yang Berbahaya</label>
                            <textarea name="tindakan_berbahaya"
                                      class="form-control @error('tindakan_berbahaya') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Uraikan tindakan/perilaku yang menyebabkan kejadian..." required>{{ old('tindakan_berbahaya') }}</textarea>
                            @error('tindakan_berbahaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Uraian Terjadinya Kecelakaan / Insiden / Ketidaksesuaian</label>
                            <textarea name="uraian_kejadian"
                                      class="form-control @error('uraian_kejadian') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Ceritakan secara kronologis bagaimana kejadian ini berlangsung..." required>{{ old('uraian_kejadian') }}</textarea>
                            @error('uraian_kejadian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">Sumber Kecelakaan / Insiden / Ketidaksesuaian</label>
                            <textarea name="sumber_kejadian"
                                      class="form-control @error('sumber_kejadian') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Uraikan akar penyebab / sumber terjadinya kejadian ini..." required>{{ old('sumber_kejadian') }}</textarea>
                            @error('sumber_kejadian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Tipe --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#4a4a8a;">
                        <h3 class="card-title text-white"><i class="ti ti-tag me-1"></i>Tipe Kejadian</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-check form-check-card h-100">
                                    <input type="radio" class="form-check-input" name="tipe" value="accident"
                                           {{ old('tipe') === 'accident' ? 'checked' : '' }} required>
                                    <div class="form-check-label">
                                        <div class="fw-bold text-danger">Accident</div>
                                        <small class="text-muted">Kejadian yang menimbulkan kerugian pada manusia atau harta benda</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-check form-check-card h-100">
                                    <input type="radio" class="form-check-input" name="tipe" value="incident"
                                           {{ old('tipe') === 'incident' ? 'checked' : '' }}>
                                    <div class="form-check-label">
                                        <div class="fw-bold text-warning">Incident</div>
                                        <small class="text-muted">Kejadian yang tidak diinginkan namun belum menimbulkan kerugian</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-check form-check-card h-100">
                                    <input type="radio" class="form-check-input" name="tipe" value="near_miss"
                                           {{ old('tipe') === 'near_miss' ? 'checked' : '' }}>
                                    <div class="form-check-label">
                                        <div class="fw-bold text-info">Near Miss</div>
                                        <small class="text-muted">Hampir celaka — hampir menimbulkan incident atau accident</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @error('tipe')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Upload --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-upload me-1"></i>Dokumen Pendukung</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Bukti Foto (minimal 3 foto, gabung 1 file, maks 100 MB)</label>
                            <input type="file" name="foto_bukti"
                                   class="form-control @error('foto_bukti') is-invalid @enderror" required>
                            <div class="form-text">Format: JPG, PNG, PDF. Gabungkan minimal 3 foto dalam 1 file.</div>
                            @error('foto_bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">File Formulir Hard Copy (maks 100 MB)</label>
                            <input type="file" name="file_formulir"
                                   class="form-control @error('file_formulir') is-invalid @enderror" required>
                            <div class="form-text">Upload scan/foto formulir hard copy yang sudah diisi dan ditandatangani.</div>
                            @error('file_formulir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 mb-4">
                    <i class="ti ti-send me-1"></i>Kirim Laporan Kecelakaan Kerja
                </button>
                </form>
            </div>

            {{-- ── Kolom Kanan: Riwayat ──────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top:1rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-1"></i>Riwayat Laporan Saya
                        </h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('laporan-kecelakaan.index') }}"
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
                            <div class="empty-icon"><i class="ti ti-shield-check" style="font-size:2.5rem; color:#2fb344;"></i></div>
                            <p class="empty-title">Tidak ada laporan</p>
                            <p class="empty-subtitle text-muted">Tidak ada insiden yang dilaporkan bulan ini. Tetap jaga keselamatan!</p>
                        </div>
                    </div>
                    @else
                    <div class="card-body border-bottom py-2 text-center">
                        <span class="text-muted small">{{ $riwayat->count() }} laporan bulan ini</span>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" style="max-height:75vh;">
                        @foreach($riwayat as $log)
                        <a href="{{ route('laporan-kecelakaan.show', $log) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                                <span class="badge bg-{{ $log->tipe_color }}-lt text-{{ $log->tipe_color }}">
                                    {{ $log->tipe_label }}
                                </span>
                            </div>
                            <div class="text-muted small text-truncate">{{ $log->tempat }}</div>
                            <div class="text-muted small">{{ $log->total_korban }} korban · {{ $log->waktu }}</div>
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
