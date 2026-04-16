@extends('layouts.app')

@section('title', 'Detail Pengajuan Cuti')
@section('pretitle', 'Detail')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Pengajuan</h3>
                <div class="card-actions">
                    <span class="badge text-white bg-{{ $cuti->status->color() }} fs-5">{{ $cuti->status->label() }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Permohonan</div>
                        <div class="datagrid-content">{{ $cuti->tanggal_permohonan->format('d M Y H:i') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nama PJLP</div>
                        <div class="datagrid-content">{{ $cuti->pjlp->nama }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">NIP</div>
                        <div class="datagrid-content">{{ $cuti->pjlp->nip }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Unit</div>
                        <div class="datagrid-content">{{ $cuti->pjlp->unit->label() }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Jenis Cuti</div>
                        <div class="datagrid-content">{{ $cuti->jenisCuti->nama }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nomor Telepon</div>
                        <div class="datagrid-content">{{ $cuti->no_telp }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Mulai</div>
                        <div class="datagrid-content">{{ $cuti->tgl_mulai->format('d M Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Selesai</div>
                        <div class="datagrid-content">{{ $cuti->tgl_selesai->format('d M Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Jumlah Hari</div>
                        <div class="datagrid-content"><strong>{{ $cuti->jumlah_hari }} hari</strong></div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label">Alasan Cuti</label>
                    <div class="p-3 bg-light rounded">
                        {{ $cuti->alasan }}
                    </div>
                </div>

                @if($cuti->danru)
                <div class="mt-3">
                    <label class="form-label text-muted small">Ditujukan ke Danru</label>
                    <div class="fw-medium">{{ $cuti->danru->nama }}</div>
                </div>
                @endif

                @php
                    $isBerjenjang = in_array($cuti->status->value, ['menunggu_danru','menunggu_chief','menunggu_koordinator','disetujui','ditolak'])
                        && $cuti->danru_id !== null;
                    $isDanruFlow  = $cuti->danru_id !== null || $cuti->approved_by_danru !== null || $cuti->approved_by_chief !== null;
                @endphp

                @if($isDanruFlow)
                <hr class="my-4">
                <h4>Alur Persetujuan</h4>
                <div class="steps steps-counter my-3">
                    {{-- Level Danru --}}
                    <div class="step-item {{ $cuti->approved_at_danru ? 'active' : ($cuti->status->value === 'menunggu_danru' ? '' : ($cuti->status->value === 'ditolak' && !$cuti->approved_at_danru ? 'text-danger' : 'active')) }}">
                        <div class="fw-medium">
                            Danru
                            @if($cuti->approved_at_danru)
                            <span class="badge bg-success-lt text-success ms-1">Disetujui</span>
                            @elseif($cuti->status->value === 'menunggu_danru')
                            <span class="badge bg-warning-lt text-warning ms-1">Menunggu</span>
                            @elseif($cuti->status->value === 'ditolak' && !$cuti->approved_at_danru)
                            <span class="badge bg-danger-lt text-danger ms-1">Ditolak</span>
                            @endif
                        </div>
                        @if($cuti->approvedByDanru)
                        <div class="text-muted small">{{ $cuti->approvedByDanru->name }} · {{ $cuti->approved_at_danru?->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                    {{-- Level Chief --}}
                    <div class="step-item {{ $cuti->approved_at_chief ? 'active' : ($cuti->status->value === 'menunggu_chief' ? '' : '') }}">
                        <div class="fw-medium">
                            Chief
                            @if($cuti->approved_at_chief)
                            <span class="badge bg-success-lt text-success ms-1">Disetujui</span>
                            @elseif($cuti->status->value === 'menunggu_chief')
                            <span class="badge bg-warning-lt text-warning ms-1">Menunggu</span>
                            @elseif($cuti->status->value === 'ditolak' && $cuti->approved_at_danru && !$cuti->approved_at_chief)
                            <span class="badge bg-danger-lt text-danger ms-1">Ditolak</span>
                            @endif
                        </div>
                        @if($cuti->approvedByChief)
                        <div class="text-muted small">{{ $cuti->approvedByChief->name }} · {{ $cuti->approved_at_chief?->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                    {{-- Level Koordinator --}}
                    <div class="step-item {{ $cuti->status === \App\Enums\StatusCuti::DISETUJUI ? 'active' : '' }}">
                        <div class="fw-medium">
                            Koordinator
                            @if($cuti->status === \App\Enums\StatusCuti::DISETUJUI)
                            <span class="badge bg-success-lt text-success ms-1">Disetujui</span>
                            @elseif($cuti->status->value === 'menunggu_koordinator')
                            <span class="badge bg-warning-lt text-warning ms-1">Menunggu</span>
                            @elseif($cuti->status === \App\Enums\StatusCuti::DITOLAK && $cuti->approved_at_chief)
                            <span class="badge bg-danger-lt text-danger ms-1">Ditolak</span>
                            @endif
                        </div>
                        @if($cuti->approvedBy && $cuti->status === \App\Enums\StatusCuti::DISETUJUI)
                        <div class="text-muted small">{{ $cuti->approvedBy->name }} · {{ $cuti->approved_at?->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                </div>
                @elseif(!$cuti->status->isPending())
                <hr class="my-4">
                <h4>Informasi Persetujuan</h4>
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Diproses Oleh</div>
                        <div class="datagrid-content">{{ $cuti->approvedBy?->name ?? '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Waktu Proses</div>
                        <div class="datagrid-content">{{ $cuti->approved_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                </div>
                @endif

                @if($cuti->alasan_penolakan)
                <div class="mt-3">
                    <label class="form-label text-danger">Alasan Penolakan</label>
                    <div class="p-3 bg-danger-lt rounded">
                        {{ $cuti->alasan_penolakan }}
                    </div>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('cuti.index') }}" class="btn btn-link">
                    <i class="ti ti-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @can('approve', $cuti)
    @if($cuti->status->isPending())
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if(auth()->user()->hasRole('danru')) Aksi Danru
                    @elseif(auth()->user()->hasRole('chief')) Aksi Chief
                    @else Aksi Koordinator
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <!-- Approve -->
                <form action="{{ route('cuti.approve', $cuti) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Yakin ingin menyetujui cuti ini?')">
                        <i class="ti ti-check me-2"></i> Setujui Cuti
                    </button>
                </form>

                <hr>

                <!-- Reject -->
                <form action="{{ route('cuti.reject', $cuti) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Alasan Penolakan</label>
                        <textarea name="alasan_penolakan" rows="3" class="form-control @error('alasan_penolakan') is-invalid @enderror"
                                  placeholder="Jelaskan alasan penolakan..." required>{{ old('alasan_penolakan') }}</textarea>
                        @error('alasan_penolakan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin menolak cuti ini?')">
                        <i class="ti ti-x me-2"></i> Tolak Cuti
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endcan
</div>
@endsection
