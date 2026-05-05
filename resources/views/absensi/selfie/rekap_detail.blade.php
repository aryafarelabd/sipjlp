@extends('layouts.app')

@section('title', 'Detail Absensi - ' . $pjlp->nama)

@push('styles')
<style>
    .absen-time { font-size: 0.95rem; font-weight: 600; line-height: 1.2; }
    tr.is-today { background-color: rgba(var(--tblr-primary-rgb), 0.05); }
    tr.is-libur td { color: var(--tblr-muted); }
</style>
@endpush

@section('content')
<div class="container-xl">

    {{-- Header --}}
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ route('absensi.rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <h2 class="page-title mb-0">{{ $pjlp->nama }}</h2>
                <div class="text-muted small">
                    {{ $pjlp->nip ?? '-' }} &bull; {{ ucfirst($pjlp->unit->value) }} &bull;
                    {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Summary chips --}}
    @php
        $totalHariKerja   = collect($hariList)->filter(fn($h) => $h['is_kerja'] || $h['absensi'])->count();
        $totalAlpha       = collect($hariList)->filter(fn($h) => $h['absensi']?->status?->value === 'alpha')->count();
        $totalIzin        = collect($hariList)->filter(fn($h) => in_array($h['absensi']?->status?->value, ['izin','cuti']))->count();
        $totalTelatMenit  = collect($hariList)->sum(fn($h) => $h['absensi']?->menit_terlambat ?? 0);

        $totalPulangCepat = 0;
        foreach ($hariList as $h) {
            $abs = $h['absensi'];
            $sft = $h['shift'];
            if (!$abs || !$sft) continue;
            $tgl = $h['tanggal'];
            $shiftSelesai = \Carbon\Carbon::parse($tgl->format('Y-m-d') . ' ' . \Carbon\Carbon::parse($sft->jam_selesai)->format('H:i:s'));
            $shiftMulai   = \Carbon\Carbon::parse($tgl->format('Y-m-d') . ' ' . \Carbon\Carbon::parse($sft->jam_mulai)->format('H:i:s'));
            if ($shiftSelesai->lte($shiftMulai)) $shiftSelesai->addDay();
            if ($abs->jam_masuk && !$abs->jam_pulang) {
                $totalPulangCepat += 225;
            } elseif ($abs->jam_masuk && $abs->jam_pulang) {
                $jamPulang = \Carbon\Carbon::parse($abs->jam_pulang);
                $selisih = (int) $jamPulang->diffInMinutes($shiftSelesai, false);
                if ($selisih > 0) $totalPulangCepat += $selisih;
            }
        }
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-auto">
            <div class="card p-2 px-3 text-center">
                <div class="text-muted small">Hari Kerja</div>
                <div class="fw-bold fs-4">{{ $totalHariKerja }}</div>
            </div>
        </div>
        <div class="col-auto">
            <div class="card p-2 px-3 text-center border-danger">
                <div class="text-muted small">Alpha</div>
                <div class="fw-bold fs-4 text-danger">{{ $totalAlpha }}</div>
            </div>
        </div>
        <div class="col-auto">
            <div class="card p-2 px-3 text-center border-info">
                <div class="text-muted small">Izin/Cuti</div>
                <div class="fw-bold fs-4 text-info">{{ $totalIzin }}</div>
            </div>
        </div>
        <div class="col-auto">
            <div class="card p-2 px-3 text-center border-warning">
                <div class="text-muted small">Telat</div>
                <div class="fw-bold fs-4 text-warning">{{ $totalTelatMenit }} <small class="fs-6 fw-normal">mnt</small></div>
            </div>
        </div>
        <div class="col-auto">
            <div class="card p-2 px-3 text-center border-orange">
                <div class="text-muted small">Pulang Cepat</div>
                <div class="fw-bold fs-4 text-orange">{{ $totalPulangCepat }} <small class="fs-6 fw-normal">mnt</small></div>
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th style="width:120px">Tanggal</th>
                        <th>Shift</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Telat</th>
                        <th>Pulang Cepat</th>
                        <th>Status</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hariList as $hari)
                        @php
                            $tanggal  = $hari['tanggal'];
                            $shift    = $hari['shift'];
                            $isKerja  = $hari['is_kerja'];
                            $absensi  = $hari['absensi'];
                            $isToday  = $tanggal->isToday();
                            $isFuture = $tanggal->isFuture();

                            // Hitung telat
                            $menitTelat = null;
                            if ($absensi && $absensi->status?->value === 'terlambat') {
                                $menitTelat = $absensi->menit_terlambat ?? 0;
                            }

                            // Hitung pulang cepat
                            $menitPulangCepat = null;
                            $pulangCepatMaks  = false;
                            if ($shift && $absensi) {
                                $shiftMulai   = \Carbon\Carbon::parse($tanggal->format('Y-m-d') . ' ' . \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i:s'));
                                $shiftSelesai = \Carbon\Carbon::parse($tanggal->format('Y-m-d') . ' ' . \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i:s'));
                                if ($shiftSelesai->lte($shiftMulai)) $shiftSelesai->addDay();

                                if ($absensi->jam_masuk && !$absensi->jam_pulang && !$isFuture) {
                                    $menitPulangCepat = 225;
                                    $pulangCepatMaks  = true;
                                } elseif ($absensi->jam_masuk && $absensi->jam_pulang) {
                                    $jamPulang = \Carbon\Carbon::parse($absensi->jam_pulang);
                                    $selisih   = (int) $jamPulang->diffInMinutes($shiftSelesai, false);
                                    if ($selisih > 0) $menitPulangCepat = $selisih;
                                }
                            }
                        @endphp
                        <tr class="{{ $isToday ? 'is-today' : '' }} {{ !$isKerja && !$absensi ? 'is-libur' : '' }}">
                            <td class="text-nowrap">
                                <div class="{{ $isToday ? 'fw-bold text-primary' : '' }}">
                                    {{ $tanggal->translatedFormat('d M') }}
                                </div>
                                <div class="text-muted small">{{ $tanggal->translatedFormat('l') }}</div>
                            </td>

                            <td>
                                @if($shift)
                                    <div class="fw-medium">{{ $shift->nama }}</div>
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Masuk --}}
                            <td>
                                @if($absensi?->jam_masuk)
                                    <div class="absen-time text-success">
                                        {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Pulang --}}
                            <td>
                                @if($absensi?->jam_pulang)
                                    <div class="absen-time text-blue">
                                        {{ \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Telat --}}
                            <td>
                                @if($menitTelat !== null && $menitTelat > 0)
                                    <span class="badge bg-warning text-dark" style="font-size:0.8rem">
                                        {{ $menitTelat }} mnt
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Pulang Cepat --}}
                            <td>
                                @if($menitPulangCepat !== null)
                                    @if($pulangCepatMaks)
                                        <span class="badge bg-danger" style="font-size:0.8rem" title="Belum absen pulang">
                                            225 mnt*
                                        </span>
                                    @else
                                        <span class="badge bg-orange" style="font-size:0.8rem">
                                            {{ $menitPulangCepat }} mnt
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($isFuture && !$absensi)
                                    <span class="text-muted small">—</span>
                                @elseif(!$isKerja && !$absensi)
                                    <span class="text-muted small">Libur</span>
                                @elseif($absensi)
                                    @php
                                        $statusColor = match($absensi->status?->value) {
                                            'hadir'     => 'success',
                                            'terlambat' => 'warning',
                                            'alpha'     => 'danger',
                                            'cuti'      => 'info',
                                            'izin'      => 'azure',
                                            default     => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }} text-white">
                                        {{ ucfirst($absensi->status?->value ?? '-') }}
                                    </span>
                                    @if($absensi->sumber_data?->value === 'manual')
                                        <span class="badge bg-secondary-lt text-muted ms-1" style="font-size:0.65rem">Manual</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Belum</span>
                                @endif
                            </td>

                            {{-- Koreksi --}}
                            <td>
                                @if(!$isFuture && auth()->user()->isAdmin())
                                <button type="button"
                                    class="btn btn-sm btn-ghost-secondary"
                                    title="Koreksi"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalKoreksi"
                                    data-tanggal="{{ $tanggal->format('Y-m-d') }}"
                                    data-tanggal-label="{{ $tanggal->translatedFormat('d M Y') }}"
                                    data-jam-masuk="{{ $absensi?->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '' }}"
                                    data-jam-pulang="{{ $absensi?->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '' }}"
                                    data-status="{{ $absensi?->status?->value ?? '' }}"
                                    data-keterangan="{{ $absensi?->keterangan ?? '' }}">
                                    <i class="ti ti-edit"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-muted small mt-2">
        * Belum absen pulang — dihitung maksimal 225 menit sementara.
    </div>
</div>

{{-- Modal Koreksi Absensi --}}
<div class="modal modal-blur fade" id="modalKoreksi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('absensi.koreksi') }}">
                @csrf
                <input type="hidden" name="pjlp_id" value="{{ $pjlp->id }}">
                <input type="hidden" name="tanggal" id="koreksiTanggal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-1"></i>Koreksi Absensi — <span id="koreksiTanggalLabel"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-muted small mb-3">{{ $pjlp->nama }} &bull; {{ ucfirst($pjlp->unit->value) }}</div>
                    <div class="mb-3">
                        <label class="form-label required">Status</label>
                        <div class="row g-2">
                            @foreach(['hadir' => ['success','Hadir'], 'terlambat' => ['warning','Terlambat'], 'alpha' => ['danger','Alpha'], 'izin' => ['info','Izin'], 'cuti' => ['primary','Cuti'], 'libur' => ['secondary','Libur']] as $val => [$color, $label])
                            <div class="col-auto">
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="{{ $val }}" id="status_{{ $val }}" required>
                                    <span class="form-check-label">
                                        <span class="badge bg-{{ $color }} text-white">{{ $label }}</span>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="koreksiJamMasuk" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Pulang</label>
                            <input type="time" name="jam_pulang" id="koreksiJamPulang" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="koreksiKeterangan" class="form-control" rows="2" placeholder="Alasan koreksi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Simpan Koreksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Koreksi
    const modalKoreksi = document.getElementById('modalKoreksi');
    if (modalKoreksi) {
        modalKoreksi.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('koreksiTanggal').value       = btn.dataset.tanggal;
            document.getElementById('koreksiTanggalLabel').textContent = btn.dataset.tanggalLabel;
            document.getElementById('koreksiJamMasuk').value      = btn.dataset.jamMasuk || '';
            document.getElementById('koreksiJamPulang').value     = btn.dataset.jamPulang || '';
            document.getElementById('koreksiKeterangan').value    = btn.dataset.keterangan || '';
            // Set radio status
            const status = btn.dataset.status;
            if (status) {
                const radio = document.getElementById('status_' + status);
                if (radio) radio.checked = true;
            } else {
                document.querySelectorAll('#modalKoreksi input[type=radio]').forEach(r => r.checked = false);
            }
        });
    }

});
</script>
@endpush
