@extends('layouts.app')

@section('title', 'Tambah Lokasi')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tambah Lokasi</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <form action="{{ route('master.lokasi.store') }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-map-pin me-1"></i>Data Lokasi</h3>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label required">Nama Lokasi</label>
                                <input type="text" name="nama"
                                       class="form-control @error('nama') is-invalid @enderror"
                                       value="{{ old('nama') }}"
                                       placeholder="contoh: Pos Depan, IGD, Parkir Utama"
                                       required autofocus>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Kode Lokasi</label>
                                <input type="text" name="kode"
                                       class="form-control @error('kode') is-invalid @enderror"
                                       value="{{ old('kode') }}"
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
                                           value="{{ old('gedung') }}"
                                           placeholder="contoh: Utama, Pendukung">
                                    @error('gedung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-5">
                                    <label class="form-label">Lantai <span class="text-muted">(opsional)</span></label>
                                    <input type="text" name="lantai"
                                           class="form-control @error('lantai') is-invalid @enderror"
                                           value="{{ old('lantai') }}"
                                           placeholder="contoh: 1, 2, Rooftop">
                                    @error('lantai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="isActive" checked>
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
                                <i class="ti ti-device-floppy me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
