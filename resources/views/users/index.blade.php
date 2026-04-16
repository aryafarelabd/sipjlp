@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen User</h2>
                <div class="text-muted mt-1">Kelola akun login dan hak akses sistem</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Tambah User
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <label class="form-label mb-1 small">Cari Nama / Email</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Ketik nama atau email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label mb-1 small">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">Semua Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar User</h3>
                <div class="card-options">
                    <span class="text-muted small">{{ $users->total() }} user</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-hover card-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-{{ $user->roles->first()?->name === 'admin' ? 'red' : ($user->roles->first()?->name === 'koordinator' ? 'blue' : ($user->roles->first()?->name === 'manajemen' ? 'purple' : 'green')) }}-lt">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        @if($user->pjlp)
                                        <div class="text-muted small">NIP: {{ $user->pjlp->nip ?? '-' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColors = [
                                        'admin'       => 'red',
                                        'koordinator' => 'blue',
                                        'manajemen'   => 'purple',
                                        'pjlp'        => 'green',
                                    ];
                                @endphp
                                @foreach($user->roles as $role)
                                <span class="badge bg-{{ $roleColors[$role->name] ?? 'secondary' }}-lt text-{{ $roleColors[$role->name] ?? 'secondary' }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->unit)
                                <span class="badge bg-secondary-lt text-secondary">{{ $user->unit->label() }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                <span class="badge bg-success-lt text-success">Aktif</span>
                                @else
                                <span class="badge bg-danger-lt text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>Edit
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ti ti-users-off fs-1 d-block mb-2 text-muted"></i>
                                Tidak ada user ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
