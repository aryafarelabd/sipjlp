@extends('layouts.app')

@section('title', 'Laporan Parkir Menginap')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Laporan Parkir Menginap</h2>
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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="parkirTab">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-roda4">
                    <i class="ti ti-car me-1"></i>Roda 4
                    @if($laporanRoda4->count() > 0)
                    <span class="badge bg-blue ms-1">{{ $laporanRoda4->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-roda2">
                    <i class="ti ti-motorbike me-1"></i>Roda 2
                    @if($laporanRoda2->count() > 0)
                    <span class="badge bg-blue ms-1">{{ $laporanRoda2->count() }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- TAB RODA 4 --}}
            <div class="tab-pane active" id="tab-roda4">
                <div class="row g-3">
                    {{-- Form input --}}
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="ti ti-car me-2"></i>Input Laporan Roda 4</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('laporan-parkir.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="jenis" value="roda_4">

                                    <div class="mb-3">
                                        <label class="form-label">Shift <span class="text-danger">*</span></label>
                                        <select name="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                            <option value="">Pilih shift</option>
                                            @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" @selected(old('jenis') === 'roda_4' && old('shift_id') == $shift->id)>
                                                {{ $shift->nama }} ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Kendaraan Roda 4 <span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah_kendaraan" class="form-control @error('jumlah_kendaraan') is-invalid @enderror"
                                               value="{{ old('jumlah_kendaraan') }}" min="0" placeholder="0" required>
                                        @error('jumlah_kendaraan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Foto Bukti <span class="text-danger">*</span>
                                            <small class="text-muted">(maks. 10 foto, maks. 5MB/foto)</small>
                                        </label>
                                        <input type="file" name="fotos[]" id="fotos-roda4"
                                               class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror"
                                               accept="image/*" multiple required>
                                        @error('fotos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('fotos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="preview-roda4" class="row g-1 mt-2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Tambahan</label>
                                        <textarea name="catatan" class="form-control" rows="2"
                                                  placeholder="Opsional...">{{ old('catatan') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-send me-1"></i>Simpan Laporan Roda 4
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat laporan roda 4 hari ini --}}
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Laporan Roda 4 Hari Ini</h3>
                                @if($laporanRoda4->count() > 0)
                                <div class="card-options">
                                    <span class="badge bg-blue">Total: {{ $laporanRoda4->sum('jumlah_kendaraan') }} kendaraan</span>
                                </div>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                @forelse($laporanRoda4 as $lap)
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $lap->jumlah_kendaraan }} kendaraan</strong>
                                            <small class="text-muted ms-2">{{ $lap->created_at->format('H:i') }}</small>
                                        </div>
                                        <span class="badge bg-blue-lt text-blue">{{ $lap->shift->nama ?? '-' }}</span>
                                    </div>
                                    @if($lap->catatan)
                                    <p class="text-muted small mb-2">{{ $lap->catatan }}</p>
                                    @endif
                                    @if($lap->fotos->count() > 0)
                                    <div class="row g-1">
                                        @foreach($lap->fotos as $foto)
                                        <div class="col-auto">
                                            <a href="{{ Storage::url($foto->path) }}" target="_blank">
                                                <img src="{{ Storage::url($foto->path) }}" class="rounded"
                                                     style="width:60px;height:60px;object-fit:cover;">
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-clipboard-off me-1"></i>Belum ada laporan roda 4 hari ini
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB RODA 2 --}}
            <div class="tab-pane" id="tab-roda2">
                <div class="row g-3">
                    {{-- Form input --}}
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="ti ti-motorbike me-2"></i>Input Laporan Roda 2</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('laporan-parkir.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="jenis" value="roda_2">

                                    <div class="mb-3">
                                        <label class="form-label">Shift <span class="text-danger">*</span></label>
                                        <select name="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                            <option value="">Pilih shift</option>
                                            @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" @selected(old('jenis') === 'roda_2' && old('shift_id') == $shift->id)>
                                                {{ $shift->nama }} ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Kendaraan Roda 2 <span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah_kendaraan" class="form-control"
                                               value="{{ old('jumlah_kendaraan') }}" min="0" placeholder="0" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Foto Bukti <span class="text-danger">*</span>
                                            <small class="text-muted">(maks. 10 foto, maks. 5MB/foto)</small>
                                        </label>
                                        <input type="file" name="fotos[]" id="fotos-roda2"
                                               class="form-control" accept="image/*" multiple required>
                                        <div id="preview-roda2" class="row g-1 mt-2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Tambahan</label>
                                        <textarea name="catatan" class="form-control" rows="2"
                                                  placeholder="Opsional...">{{ old('catatan') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-send me-1"></i>Simpan Laporan Roda 2
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat laporan roda 2 hari ini --}}
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Laporan Roda 2 Hari Ini</h3>
                                @if($laporanRoda2->count() > 0)
                                <div class="card-options">
                                    <span class="badge bg-blue">Total: {{ $laporanRoda2->sum('jumlah_kendaraan') }} kendaraan</span>
                                </div>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                @forelse($laporanRoda2 as $lap)
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $lap->jumlah_kendaraan }} kendaraan</strong>
                                            <small class="text-muted ms-2">{{ $lap->created_at->format('H:i') }}</small>
                                        </div>
                                        <span class="badge bg-blue-lt text-blue">{{ $lap->shift->nama ?? '-' }}</span>
                                    </div>
                                    @if($lap->catatan)
                                    <p class="text-muted small mb-2">{{ $lap->catatan }}</p>
                                    @endif
                                    @if($lap->fotos->count() > 0)
                                    <div class="row g-1">
                                        @foreach($lap->fotos as $foto)
                                        <div class="col-auto">
                                            <a href="{{ Storage::url($foto->path) }}" target="_blank">
                                                <img src="{{ Storage::url($foto->path) }}" class="rounded"
                                                     style="width:60px;height:60px;object-fit:cover;">
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-clipboard-off me-1"></i>Belum ada laporan roda 2 hari ini
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
function setupPreview(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        const files = Array.from(this.files);

        if (files.length > 10) {
            alert('Maksimal 10 foto!');
            this.value = '';
            return;
        }

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const col = document.createElement('div');
                col.className = 'col-auto';
                col.innerHTML = `<img src="${e.target.result}" class="rounded"
                    style="width:70px;height:70px;object-fit:cover;border:1px solid #dee2e6;">`;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });
}

setupPreview('fotos-roda4', 'preview-roda4');
setupPreview('fotos-roda2', 'preview-roda2');
</script>
@endpush
@endsection
