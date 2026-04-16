@extends('layouts.app')

@section('title', 'Detail Lembar Kerja CS')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('lembar-kerja-cs.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Cleaning Service</div>
                <h2 class="page-title">Detail Lembar Kerja CS</h2>
            </div>
            <div class="col-auto">
                <span class="badge bg-{{ $lembarKerjaC->status_color }} fs-6">
                    {{ $lembarKerjaC->status_label }}
                </span>
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

        <div class="row g-3">
            <div class="col-lg-8">

                {{-- Header Info --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <div class="text-muted small">Petugas</div>
                                <div class="fw-medium">{{ $lembarKerjaC->pjlp->nama ?? '-' }}</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="text-muted small">Tanggal</div>
                                <div class="fw-medium">{{ $lembarKerjaC->tanggal->translatedFormat('d F Y') }}</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="text-muted small">Area</div>
                                <div class="fw-medium">{{ $lembarKerjaC->area->nama ?? '-' }}</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="text-muted small">Shift</div>
                                <div class="fw-medium">{{ $lembarKerjaC->shift->nama ?? '-' }}</div>
                            </div>
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
                            <span class="badge bg-white text-dark">
                                {{ count($lembarKerjaC->kegiatan_periodik ?? []) }} kegiatan
                            </span>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($lembarKerjaC->kegiatan_periodik ?? [] as $i => $kg)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-blue-lt text-blue rounded-pill">{{ $i + 1 }}</span>
                                <span class="fw-medium">{{ $kg['nama'] ?? '-' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-muted text-center py-3">Tidak ada data</div>
                        @endforelse
                    </div>
                </div>

                {{-- Kegiatan Extra Job --}}
                @if(!empty($lembarKerjaC->kegiatan_extra_job))
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#0ca678;">
                        <h3 class="card-title text-white">
                            <i class="ti ti-star me-2"></i>Kegiatan Extra Job
                        </h3>
                        <div class="card-actions">
                            <span class="badge bg-white text-dark">
                                {{ count($lembarKerjaC->kegiatan_extra_job) }} kegiatan
                            </span>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($lembarKerjaC->kegiatan_extra_job as $i => $kg)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-green-lt text-green rounded-pill">{{ $i + 1 }}</span>
                                <span class="fw-medium">{{ $kg['nama'] ?? '-' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Dokumentasi --}}
                @if(!empty($lembarKerjaC->foto_dokumentasi) || $lembarKerjaC->deskripsi_foto || $lembarKerjaC->catatan)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-camera me-1"></i>Dokumentasi</h3>
                    </div>
                    <div class="card-body">
                        @if($lembarKerjaC->deskripsi_foto)
                        <div class="mb-3">
                            <div class="text-muted small">Deskripsi Foto</div>
                            <div>{{ $lembarKerjaC->deskripsi_foto }}</div>
                        </div>
                        @endif

                        @if(!empty($lembarKerjaC->foto_dokumentasi))
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            @foreach($lembarKerjaC->foto_dokumentasi as $i => $foto)
                            <a href="{{ Storage::url($foto) }}" target="_blank" title="Foto {{ $i + 1 }}">
                                <img src="{{ Storage::url($foto) }}"
                                     alt="Foto {{ $i + 1 }}"
                                     style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
                            </a>
                            @endforeach
                        </div>
                        @endif

                        @if($lembarKerjaC->catatan)
                        <div>
                            <div class="text-muted small">Catatan</div>
                            <div>{{ $lembarKerjaC->catatan }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Catatan Koordinator --}}
                @if($lembarKerjaC->catatan_koordinator)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title text-{{ $lembarKerjaC->isRejected() ? 'danger' : 'success' }}">
                            <i class="ti ti-message me-1"></i>Catatan Koordinator
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $lembarKerjaC->catatan_koordinator }}</p>
                        @if($lembarKerjaC->validated_at)
                        <div class="text-muted small mt-2">
                            {{ $lembarKerjaC->validator?->name }} —
                            {{ $lembarKerjaC->validated_at->translatedFormat('d M Y H:i') }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- ── Panel Aksi Koordinator ── --}}
            @if($lembarKerjaC->canValidate() && auth()->user()->hasAnyRole(['koordinator','admin']))
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aksi Koordinator</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('lembar-kerja-cs.validate', $lembarKerjaC) }}" class="mb-3">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Catatan (opsional)</label>
                                <textarea name="catatan_koordinator" class="form-control form-control-sm" rows="2"
                                          placeholder="Catatan validasi..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Validasi lembar kerja ini?')">
                                <i class="ti ti-check me-1"></i>Validasi
                            </button>
                        </form>
                        <hr>
                        <form method="POST" action="{{ route('lembar-kerja-cs.reject', $lembarKerjaC) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label required">Alasan Penolakan</label>
                                <textarea name="catatan_koordinator" class="form-control form-control-sm" rows="2"
                                          placeholder="Jelaskan alasan penolakan..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Tolak lembar kerja ini?')">
                                <i class="ti ti-x me-1"></i>Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
