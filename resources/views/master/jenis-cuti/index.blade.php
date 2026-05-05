@extends('layouts.app')

@section('title', 'Master Jenis Cuti')

@section('actions')
<a href="{{ route('master.jenis-cuti.create') }}" class="btn btn-primary">
    <i class="ti ti-plus me-2"></i> Tambah Jenis Cuti
</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Maks. Hari/Tahun</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenisCuti as $jenis)
                <tr>
                    <td>{{ $jenis->nama }}</td>
                    <td>{{ $jenis->max_hari ?? 'Tidak terbatas' }}</td>
                    <td>{{ $jenis->keterangan ?? '-' }}</td>
                    <td>
                        @if($jenis->is_active)
                        <span class="badge bg-success text-white">Aktif</span>
                        @else
                        <span class="badge bg-danger text-white">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-list">
                            <a href="{{ route('master.jenis-cuti.edit', $jenis) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('master.jenis-cuti.destroy', $jenis) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus jenis cuti ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Tidak ada data jenis cuti</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
