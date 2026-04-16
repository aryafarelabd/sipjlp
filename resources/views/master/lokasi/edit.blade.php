@extends('layouts.app')

@section('title', 'Edit Lokasi')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Edit Lokasi</h2>
                <div class="text-muted mt-1">{{ $lokasi->nama }}</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <form action="{{ route('master.lokasi.update', $lokasi) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-map-pin me-1"></i>Data Lokasi</h3>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label required">Nama Lokasi</label>
                                <input type="text" name="nama"
                                       class="form-control @error('nama') is-invalid @enderror"
                                       value="{{ old('nama', $lokasi->nama) }}"
                                       placeholder="contoh: Pos Depan, IGD, Parkir Utama"
                                       required autofocus>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Kode Lokasi</label>
                                <input type="text" name="kode"
                                       class="form-control @error('kode') is-invalid @enderror"
                                       value="{{ old('kode', $lokasi->kode) }}"
                                       placeholder="contoh: POS-01, IGD-01"
                                       style="text-transform:uppercase"
                                       required>
                                <div class="form-hint">Kode unik singkat untuk identifikasi lokasi</div>
                                @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-7">
                                    <label class="form-label">Gedung <span class="text-muted">(opsional)</span></label>
                                    <input type="text" name="gedung"
                                           class="form-control @error('gedung') is-invalid @enderror"
                                           value="{{ old('gedung', $lokasi->gedung) }}"
                                           placeholder="contoh: Utama, Pendukung">
                                    @error('gedung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-5">
                                    <label class="form-label">Lantai <span class="text-muted">(opsional)</span></label>
                                    <input type="text" name="lantai"
                                           class="form-control @error('lantai') is-invalid @enderror"
                                           value="{{ old('lantai', $lokasi->lantai) }}"
                                           placeholder="contoh: 1, 2, Rooftop">
                                    @error('lantai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="isActive"
                                           {{ old('is_active', $lokasi->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Lokasi aktif</label>
                                </div>
                                <div class="form-hint">Nonaktifkan jika lokasi tidak digunakan lagi</div>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('master.lokasi.index') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-arrow-left me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
