@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">User</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Kata Kunci Aktivitas</label>
                <input type="text" name="aktivitas" class="form-control" placeholder="cth: Update jadwal..."
                    value="{{ request('aktivitas') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control" value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="ti ti-search me-1"></i>Filter
                </button>
                <a href="{{ route('audit-log.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-history me-2 text-muted"></i>Riwayat Aktivitas
        </h3>
        <div class="card-options">
            <span class="badge bg-blue-lt">{{ $logs->total() }} entri</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover">
            <thead>
                <tr>
                    <th style="width:160px">Waktu</th>
                    <th style="width:180px">User</th>
                    <th>Aktivitas</th>
                    <th style="width:160px">Modul / Data</th>
                    <th style="width:120px">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    {{-- Waktu --}}
                    <td>
                        <div class="fw-medium">{{ $log->waktu->format('d M Y') }}</div>
                        <div class="text-muted small">{{ $log->waktu->format('H:i:s') }}</div>
                    </td>

                    {{-- User --}}
                    <td>
                        @if($log->user)
                            @php
                                $role = $log->user->getRoleNames()->first();
                                $roleColor = match($role) {
                                    'admin'       => 'red',
                                    'koordinator' => 'blue',
                                    'pjlp'        => 'green',
                                    'manajemen'   => 'purple',
                                    default       => 'secondary',
                                };
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-xs rounded-circle bg-{{ $roleColor }}-lt text-{{ $roleColor }} fw-bold" style="font-size:10px">
                                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                </span>
                                <div>
                                    <div class="fw-medium" style="font-size:13px">{{ $log->user->name }}</div>
                                    <div class="text-muted small">{{ ucfirst($role ?? '-') }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>

                    {{-- Aktivitas --}}
                    <td>
                        <span>{{ $log->aktivitas }}</span>
                    </td>

                    {{-- Modul / Data --}}
                    <td>
                        @if($log->model_type)
                            @php
                                $modelName = class_basename($log->model_type);
                                $modelLabel = match($modelName) {
                                    'User'                => 'User',
                                    'Pjlp'                => 'Data PJLP',
                                    'Absensi'             => 'Absensi',
                                    'Jadwal'              => 'Jadwal Security',
                                    'JadwalShiftCs'       => 'Jadwal CS',
                                    'Cuti'                => 'Cuti',
                                    'LembarKerjaCs'       => 'Lembar Kerja CS',
                                    'LembarKerjaSecurity' => 'Lembar Kerja Security',
                                    'LogbookLimbah'       => 'Logbook Limbah',
                                    'LogbookB3'           => 'Logbook B3',
                                    default               => $modelName,
                                };
                            @endphp
                            <span class="badge bg-blue-lt text-blue">{{ $modelLabel }}</span>
                            @if($log->model_id)
                                <div class="text-muted small">#{{ $log->model_id }}</div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- IP Address --}}
                    <td>
                        <span class="text-muted small font-monospace">{{ $log->ip_address ?? '—' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                        Tidak ada data audit log
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer d-flex align-items-center">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
