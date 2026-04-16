@extends('layouts.app')

@section('title', 'Detail Pemeriksaan Hydrant')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('inspeksi-hydrant.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Detail Pemeriksaan Hydrant Outdoor</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Header --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small">Petugas</div>
                        <div class="fw-medium">{{ $inspeksiHydrant->pjlp->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small">Tanggal</div>
                        <div class="fw-medium">{{ $inspeksiHydrant->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small">Shift</div>
                        <div class="fw-medium">{{ $inspeksiHydrant->shift->nama ?? '-' }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge {{ $inspeksiHydrant->lokasi_aman == 5 ? 'bg-success' : ($inspeksiHydrant->lokasi_aman >= 3 ? 'bg-warning' : 'bg-danger') }} fs-6">
                        {{ $inspeksiHydrant->lokasi_aman }}/5 Lokasi Semua Komponen Aman
                    </span>
                </div>
            </div>
        </div>

        {{-- Per Lokasi --}}
        @foreach($lokasi as $lokKey => $lokLabel)
        @php
            $data      = $inspeksiHydrant->$lokKey ?? [];
            $fotoCol   = 'foto_' . $lokKey;
            $fotos     = $inspeksiHydrant->$fotoCol ?? [];
        @endphp
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-flame me-2"></i>{{ $lokLabel }}
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th width="150">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($komponen as $kompKey => $kompConfig)
                        @php
                            $val    = $data[$kompKey] ?? null;
                            $ket    = $data[$kompKey . '_ket'] ?? null;
                            $isAman = $val === ($nilaiAman[$kompKey] ?? null);
                        @endphp
                        <tr>
                            <td class="small fw-medium">{{ $kompConfig['label'] }}</td>
                            <td>
                                @if($val)
                                <span class="badge {{ $isAman ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                    {{ $kompConfig['options'][$val] ?? $val }}
                                </span>
                                @else
                                <span class="badge bg-secondary-lt text-muted">—</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $ket ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Foto Bukti per Lokasi --}}
            @if(!empty($fotos))
            <div class="card-footer">
                <div class="text-muted small fw-medium mb-2">
                    <i class="ti ti-camera me-1"></i>Foto Bukti
                </div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($fotos as $index => $foto)
                    <a href="{{ Storage::url($foto) }}" target="_blank" title="Foto {{ $index + 1 }}">
                        <img src="{{ Storage::url($foto) }}"
                             alt="Foto {{ $index + 1 }}"
                             style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach

    </div>
</div>
@endsection
