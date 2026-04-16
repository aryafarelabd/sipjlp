@extends('layouts.app')

@section('title', 'Detail Pemeriksaan Hydrant Indoor')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('inspeksi-hydrant-indoor.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Security</div>
                <h2 class="page-title">Detail Pemeriksaan Hydrant Indoor</h2>
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
                    <div class="col-sm-3">
                        <div class="text-muted small">Petugas</div>
                        <div class="fw-medium">{{ $inspeksiHydrantIndoor->pjlp->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Tanggal</div>
                        <div class="fw-medium">{{ $inspeksiHydrantIndoor->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Shift</div>
                        <div class="fw-medium">{{ $inspeksiHydrantIndoor->shift->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small">Lokasi</div>
                        <div class="fw-medium">{{ $lokasi[$inspeksiHydrantIndoor->lokasi] ?? $inspeksiHydrantIndoor->lokasi }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    @php $amanCount = $inspeksiHydrantIndoor->hydrant_aman; @endphp
                    <span class="badge {{ $amanCount == 2 ? 'bg-success' : ($amanCount == 1 ? 'bg-warning' : 'bg-danger') }} fs-6">
                        {{ $amanCount }}/2 Hydrant Semua Komponen Aman
                    </span>
                </div>
            </div>
        </div>

        {{-- Hydrant 1 & 2 --}}
        @foreach(['hydrant_1' => 'Hydrant 1', 'hydrant_2' => 'Hydrant 2'] as $hKey => $hLabel)
        @php
            $data    = $inspeksiHydrantIndoor->$hKey ?? [];
            $fotoCol = 'foto_' . $hKey;
            $fotos   = $inspeksiHydrantIndoor->$fotoCol ?? [];
        @endphp
        <div class="card mb-3">
            <div class="card-header" style="background-color:#4a4a8a;">
                <h3 class="card-title text-white">
                    <i class="ti ti-flame me-2"></i>{{ $hLabel }}
                    @if($inspeksiHydrantIndoor->isHydrantAman($data))
                    <span class="badge bg-success ms-2">Semua Aman</span>
                    @else
                    <span class="badge bg-danger ms-2">Ada Temuan</span>
                    @endif
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th width="160">Status</th>
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
            @if(!empty($data['keterangan']))
            <div class="card-footer text-muted small">
                <i class="ti ti-notes me-1"></i>{{ $data['keterangan'] }}
            </div>
            @endif
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
