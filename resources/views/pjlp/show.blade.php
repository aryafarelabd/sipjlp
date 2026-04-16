@extends('layouts.app')

@section('title', 'Detail PJLP')
@section('pretitle', 'Data PJLP')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                @if($pjlp->foto)
                    <span class="avatar avatar-xl mb-3" style="background-image: url({{ asset('storage/pjlp/' . $pjlp->foto) }})"></span>
                @else
                    <span class="avatar avatar-xl mb-3 bg-primary-lt">{{ strtoupper(substr($pjlp->nama, 0, 2)) }}</span>
                @endif
                <h3 class="card-title mb-1">{{ $pjlp->nama }}</h3>
                <p class="text-muted">{{ $pjlp->jabatan }}</p>
                <div class="mb-3">
                    @if($pjlp->status == 'aktif')
                        <span class="badge bg-success">Aktif</span>
                    @elseif($pjlp->status == 'nonaktif')
                        <span class="badge bg-secondary">Non-Aktif</span>
                    @elseif($pjlp->status == 'cuti')
                        <span class="badge bg-warning">Cuti</span>
                    @elseif($pjlp->status == 'resign')
                        <span class="badge bg-danger">Resign</span>
                    @endif

                    @if($pjlp->unit == 'security')
                        <span class="badge bg-blue-lt">Security</span>
                    @else
                        <span class="badge bg-cyan-lt">Cleaning Service</span>
                    @endif
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row mb-2">
                    <div class="col-5 text-muted">NIP</div>
                    <div class="col-7">{{ $pjlp->nip }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Kode Badge</div>
                    <div class="col-7">
                        @if($pjlp->badge_number)
                            <span class="badge bg-blue-lt">{{ $pjlp->badge_number }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">No. Telepon</div>
                    <div class="col-7">{{ $pjlp->no_telp ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Bergabung</div>
                    <div class="col-7">{{ $pjlp->tanggal_bergabung?->translatedFormat('d M Y') ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Alamat</div>
                    <div class="col-7">{{ $pjlp->alamat ?? '-' }}</div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex">
                    <a href="{{ route('pjlp.index') }}" class="btn btn-link">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                    @can('update', $pjlp)
                    <a href="{{ route('pjlp.edit', $pjlp->id) }}" class="btn btn-primary ms-auto">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Account Info -->
        @if($pjlp->user)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Akun Login</h3>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-5 text-muted">Email</div>
                    <div class="col-7">{{ $pjlp->user->email }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Status</div>
                    <div class="col-7"><span class="badge bg-success">Aktif</span></div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <!-- Absensi Terakhir -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Absensi Terakhir</h3>
                <div class="card-actions">
                    <a href="{{ route('absensi.rekap', ['search' => $pjlp->nama]) }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($pjlp->absensi->isEmpty())
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-fingerprint" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">Belum ada data absensi</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pjlp->absensi as $absensi)
                                    <tr>
                                        <td>{{ $absensi->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                                        <td>
                                            @if($absensi->jam_masuk)
                                                <span class="{{ $absensi->is_telat ? 'text-danger' : 'text-success' }}">
                                                    {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }}
                                                </span>
                                                @if($absensi->is_telat)
                                                    <small class="text-danger d-block">Terlambat</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($absensi->jam_keluar)
                                                {{ \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $absensi->shift?->nama ?? '-' }}</td>
                                        <td>
                                            @if($absensi->status == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($absensi->status == 'izin')
                                                <span class="badge bg-info">Izin</span>
                                            @elseif($absensi->status == 'sakit')
                                                <span class="badge bg-warning">Sakit</span>
                                            @elseif($absensi->status == 'cuti')
                                                <span class="badge bg-secondary">Cuti</span>
                                            @elseif($absensi->status == 'alpha')
                                                <span class="badge bg-danger">Alpha</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $absensi->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Tambahan -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Tambahan</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <small class="text-muted">Dibuat:</small>
                            <div>{{ $pjlp->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <small class="text-muted">Diperbarui:</small>
                            <div>{{ $pjlp->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
