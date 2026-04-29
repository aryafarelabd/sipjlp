@extends('layouts.app')

@section('title', 'Tambah Shift')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Shift Baru</h3>
            </div>
            <form action="{{ route('master.shift.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Shift</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama') }}" required>
                        @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jam Masuk --}}
                    <div class="mb-2">
                        <label class="form-label required fw-bold">Jam Masuk</label>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror"
                                   value="{{ old('jam_mulai') }}" required>
                            @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batas Bawah (BI)</label>
                            <input type="time" name="bi" class="form-control @error('bi') is-invalid @enderror"
                                   value="{{ old('bi') }}">
                            <small class="text-muted">Awal window deteksi masuk</small>
                            @error('bi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batas Atas (AI)</label>
                            <input type="time" name="ai" class="form-control @error('ai') is-invalid @enderror"
                                   value="{{ old('ai') }}">
                            <small class="text-muted">Akhir window deteksi masuk</small>
                            @error('ai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Jam Pulang --}}
                    <div class="mb-2">
                        <label class="form-label required fw-bold">Jam Pulang</label>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror"
                                   value="{{ old('jam_selesai') }}" required>
                            @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batas Bawah (BO)</label>
                            <input type="time" name="bo" class="form-control @error('bo') is-invalid @enderror"
                                   value="{{ old('bo') }}">
                            <small class="text-muted">Awal window deteksi pulang</small>
                            @error('bo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batas Atas (AO)</label>
                            <input type="time" name="ao" class="form-control @error('ao') is-invalid @enderror"
                                   value="{{ old('ao') }}">
                            <small class="text-muted">Akhir window deteksi pulang</small>
                            @error('ao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Toleransi & Status --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Toleransi Terlambat (menit)</label>
                            <input type="number" name="toleransi_terlambat" class="form-control @error('toleransi_terlambat') is-invalid @enderror"
                                   value="{{ old('toleransi_terlambat', 15) }}" min="0" max="120" required>
                            @error('toleransi_terlambat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('master.shift.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
