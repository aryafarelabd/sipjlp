@extends('layouts.app')

@section('title', 'Master Kegiatan LK CS')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Master Data</div>
                <h2 class="page-title">Kegiatan Lembar Kerja CS</h2>
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

            {{-- ── Kegiatan Periodik ── --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-list-check me-2 text-blue"></i>Kegiatan Periodik</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Nama Kegiatan</th>
                                    <th style="width:60px">Urutan</th>
                                    <th style="width:70px">Status</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodik as $item)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td class="text-center">{{ $item->urutan }}</td>
                                    <td>
                                        <span class="badge {{ $item->is_active ? 'bg-success-lt text-success' : 'bg-secondary-lt text-muted' }}">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-ghost-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-edit-{{ $item->id }}">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('master.kegiatan-lk-cs.destroy', $item) }}" class="d-inline"
                                              onsubmit="return confirm('Hapus kegiatan ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-ghost-danger"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Edit --}}
                                <div class="modal modal-blur fade" id="modal-edit-{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('master.kegiatan-lk-cs.update', $item) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kegiatan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label required">Nama</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Urutan</label>
                                                        <input type="number" name="urutan" class="form-control" value="{{ $item->urutan }}" min="0">
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                                   {{ $item->is_active ? 'checked' : '' }}>
                                                            <span class="form-check-label">Aktif</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada kegiatan periodik</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="{{ route('master.kegiatan-lk-cs.store') }}" class="row g-2 align-items-end">
                            @csrf
                            <input type="hidden" name="tipe" value="periodik">
                            <div class="col">
                                <input type="text" name="nama" class="form-control form-control-sm"
                                       placeholder="Nama kegiatan periodik..." required>
                            </div>
                            <div class="col-auto" style="width:70px">
                                <input type="number" name="urutan" class="form-control form-control-sm" placeholder="Urut" min="0" value="0">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Kegiatan Extra Job ── --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-star me-2 text-green"></i>Kegiatan Extra Job</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Nama Kegiatan</th>
                                    <th style="width:60px">Urutan</th>
                                    <th style="width:70px">Status</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($extraJob as $item)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td class="text-center">{{ $item->urutan }}</td>
                                    <td>
                                        <span class="badge {{ $item->is_active ? 'bg-success-lt text-success' : 'bg-secondary-lt text-muted' }}">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-ghost-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-edit-ej-{{ $item->id }}">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('master.kegiatan-lk-cs.destroy', $item) }}" class="d-inline"
                                              onsubmit="return confirm('Hapus kegiatan ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-ghost-danger"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal modal-blur fade" id="modal-edit-ej-{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('master.kegiatan-lk-cs.update', $item) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kegiatan Extra Job</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label required">Nama</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Urutan</label>
                                                        <input type="number" name="urutan" class="form-control" value="{{ $item->urutan }}" min="0">
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                                   {{ $item->is_active ? 'checked' : '' }}>
                                                            <span class="form-check-label">Aktif</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada kegiatan extra job</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="{{ route('master.kegiatan-lk-cs.store') }}" class="row g-2 align-items-end">
                            @csrf
                            <input type="hidden" name="tipe" value="extra_job">
                            <div class="col">
                                <input type="text" name="nama" class="form-control form-control-sm"
                                       placeholder="Nama kegiatan extra job..." required>
                            </div>
                            <div class="col-auto" style="width:70px">
                                <input type="number" name="urutan" class="form-control form-control-sm" placeholder="Urut" min="0" value="0">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="ti ti-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
