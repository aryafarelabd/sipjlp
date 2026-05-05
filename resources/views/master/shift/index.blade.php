@extends('layouts.app')

@section('title', 'Master Shift')

@section('actions')
<a href="{{ route('master.shift.create') }}" class="btn btn-primary">
    <i class="ti ti-plus me-2"></i> Tambah Shift
</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Nama Shift</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Status</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td>{{ $shift->nama }}</td>
                    <td>{{ $shift->jam_mulai }}</td>
                    <td>{{ $shift->jam_selesai }}</td>
                    <td>
                        @if($shift->is_active)
                        <span class="badge bg-success text-white">Aktif</span>
                        @else
                        <span class="badge bg-danger text-white">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-list">
                            <a href="{{ route('master.shift.edit', $shift) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('master.shift.destroy', $shift) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus shift ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Tidak ada data shift</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
