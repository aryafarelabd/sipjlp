@extends('layouts.app')

@section('title', 'Validasi Cuti')
@section('pretitle', 'Cuti')

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Filter Validasi</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Tahap Menunggu</option>
                    @foreach([\App\Enums\StatusCuti::MENUNGGU, \App\Enums\StatusCuti::MENUNGGU_DANRU, \App\Enums\StatusCuti::MENUNGGU_CHIEF, \App\Enums\StatusCuti::MENUNGGU_KOORDINATOR] as $status)
                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal Cuti</label>
                <input type="date" name="dari" class="form-control" value="{{ request('dari') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal Cuti</label>
                <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="ti ti-filter me-1"></i>Filter
                </button>
                <a href="{{ route('cuti.validasi') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Menunggu Validasi</h3>
            <div class="text-muted small">{{ $cuti->total() }} pengajuan perlu diproses</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Tanggal Pengajuan</th>
                    <th>PJLP</th>
                    <th>Unit</th>
                    <th>Jenis Cuti</th>
                    <th>Periode</th>
                    <th>Jumlah</th>
                    <th>Tahap</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuti as $item)
                <tr>
                    <td class="text-nowrap">{{ $item->tanggal_permohonan->format('d M Y H:i') }}</td>
                    <td>
                        <div class="fw-medium">{{ $item->pjlp->nama }}</div>
                        @if($item->danru)
                        <div class="text-muted small">Danru: {{ $item->danru->nama }}</div>
                        @endif
                    </td>
                    <td><span class="badge">{{ $item->pjlp->unit->label() }}</span></td>
                    <td>{{ $item->jenisCuti->nama }}</td>
                    <td class="text-nowrap">{{ $item->tgl_mulai->format('d M Y') }} - {{ $item->tgl_selesai->format('d M Y') }}</td>
                    <td>{{ $item->jumlah_hari }} hari</td>
                    <td>
                        <span class="badge text-white bg-{{ $item->status->color() }}">
                            {{ $item->status->label() }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('cuti.show', ['cuti' => $item, 'from' => 'validasi']) }}" class="btn btn-sm btn-primary">
                            Validasi
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Tidak ada pengajuan cuti yang menunggu validasi Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cuti->hasPages())
    <div class="card-footer d-flex align-items-center">
        {{ $cuti->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
