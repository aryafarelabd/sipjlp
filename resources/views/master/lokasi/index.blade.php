@extends('layouts.app')

@section('title', 'Master Lokasi')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Master Lokasi</h2>
                <div class="text-muted mt-1">Daftar lokasi pos Security yang digunakan dalam penjadwalan</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('master.lokasi.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah Lokasi
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Nama Lokasi</th>
                            <th>Kode</th>
                            <th>Gedung</th>
                            <th>Lantai</th>
                            <th class="text-center">Status</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lokasi as $lok)
                        <tr>
                            <td class="text-muted">{{ $lokasi->firstItem() + $loop->index }}</td>
                            <td class="fw-medium">{{ $lok->nama }}</td>
                            <td><code>{{ $lok->kode ?? '-' }}</code></td>
                            <td>{{ $lok->gedung ?? '-' }}</td>
                            <td>{{ $lok->lantai ? 'Lt. ' . $lok->lantai : '-' }}</td>
                            <td class="text-center">
                                @if($lok->is_active)
                                <span class="badge bg-success-lt text-success">Aktif</span>
                                @else
                                <span class="badge bg-danger-lt text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('master.lokasi.edit', $lok) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="ti ti-pencil me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('master.lokasi.destroy', $lok) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus lokasi {{ addslashes($lok->nama) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-map-pin me-1"></i>Belum ada data lokasi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lokasi->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $lokasi->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
