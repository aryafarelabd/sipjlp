@extends('layouts.app')

@section('title', 'Ajukan Cuti')
@section('pretitle', 'Form Pengajuan')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('cuti.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Pengajuan Cuti</h3>
                </div>
                <div class="card-body">
                    <!-- Info PJLP -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div>
                                <h4 class="alert-title">Informasi PJLP</h4>
                                <div class="text-muted">
                                    <strong>Nama:</strong> {{ $pjlp->nama }}<br>
                                    <strong>NIP:</strong> {{ $pjlp->nip }}<br>
                                    <strong>Unit:</strong> {{ $pjlp->unit->label() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Permohonan (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal Permohonan</label>
                        <input type="text" class="form-control" value="{{ now()->format('d M Y H:i') }}" readonly disabled>
                        <small class="text-muted">Diisi otomatis oleh sistem</small>
                    </div>

                    @if(!empty($danruList) && $danruList->isNotEmpty())
                    <!-- Pilih Danru (khusus anggota security) -->
                    <div class="mb-3">
                        <label class="form-label required">Tujukan ke Danru</label>
                        <select name="danru_id" class="form-select @error('danru_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Danru --</option>
                            @foreach($danruList as $danru)
                            <option value="{{ $danru->id }}" {{ old('danru_id') == $danru->id ? 'selected' : '' }}>
                                {{ $danru->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('danru_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pilih danru yang bertugas pada shift Anda</small>
                    </div>
                    @endif

                    <!-- Jenis Cuti -->
                    <div class="mb-3">
                        <label class="form-label required">Jenis Cuti</label>
                        <select name="jenis_cuti_id" class="form-select @error('jenis_cuti_id') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Cuti</option>
                            @foreach($jenisCutiList as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_cuti_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                                @if($jenis->max_hari)
                                (Maks. {{ $jenis->max_hari }} hari/tahun)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('jenis_cuti_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Periode Cuti -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control @error('tgl_mulai') is-invalid @enderror"
                                   value="{{ old('tgl_mulai') }}" min="{{ date('Y-m-d') }}" required>
                            @error('tgl_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control @error('tgl_selesai') is-invalid @enderror"
                                   value="{{ old('tgl_selesai') }}" min="{{ date('Y-m-d') }}" required>
                            @error('tgl_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="alert alert-secondary">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-calculator me-2"></i>
                                <span>Jumlah hari cuti: <strong id="jumlahHari">0</strong> hari</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="mb-3">
                        <label class="form-label required">Nomor Telepon Aktif</label>
                        <input type="tel" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                               value="{{ old('no_telp', $pjlp->no_telp) }}" placeholder="08xxxxxxxxxx" required>
                        @error('no_telp')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nomor yang bisa dihubungi selama cuti</small>
                    </div>

                    <!-- Alasan -->
                    <div class="mb-3">
                        <label class="form-label required">Alasan Cuti</label>
                        <textarea name="alasan" rows="4" class="form-control @error('alasan') is-invalid @enderror"
                                  placeholder="Jelaskan alasan pengajuan cuti..." required>{{ old('alasan') }}</textarea>
                        @error('alasan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 10 karakter</small>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex">
                        <a href="{{ route('cuti.index') }}" class="btn btn-link">Batal</a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submitCuti" disabled>
                            <i class="ti ti-send me-2"></i> Kirim Pengajuan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <i class="ti ti-info-circle text-info me-2"></i>
                    <strong>Penting:</strong>
                </div>
                <ul class="mb-0">
                    <li>{{ $approvalInfo }}</li>
                    <li>Tanggal permohonan diisi otomatis oleh sistem</li>
                    <li>Data <strong>tidak dapat diubah</strong> setelah dikirim</li>
                    <li>Pastikan nomor telepon yang diisi aktif dan dapat dihubungi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tglMulai = document.querySelector('input[name="tgl_mulai"]');
    const tglSelesai = document.querySelector('input[name="tgl_selesai"]');
    const jumlahHari = document.getElementById('jumlahHari');
    const submitCuti = document.getElementById('submitCuti');

    function hitungHari() {
        let totalHari = 0;
        if (tglMulai.value && tglSelesai.value) {
            const start = new Date(tglMulai.value);
            const end = new Date(tglSelesai.value);
            const diffTime = end - start;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalHari = diffDays > 0 ? diffDays : 0;
        }

        jumlahHari.textContent = totalHari;
        submitCuti.disabled = totalHari < 1;
    }

    tglMulai.addEventListener('change', function() {
        tglSelesai.min = this.value;
        hitungHari();
    });

    tglSelesai.addEventListener('change', hitungHari);

    hitungHari();
});
</script>
@endpush
@endsection
