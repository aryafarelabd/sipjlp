@extends('layouts.app')

@section('title', 'Jadwal Shift Security')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Jadwal Shift Security</h2>
                <div class="text-muted mt-1">Input jadwal shift per PJLP per hari (dikelola oleh Administrator)</div>
            </div>
            @if($canEdit)
            <div class="col-auto ms-auto d-print-none">
                <button type="button" class="btn btn-success" id="btnPublish" title="Publikasikan jadwal agar terlihat oleh PJLP">
                    <i class="ti ti-send me-1"></i>Publikasikan Jadwal
                </button>
            </div>
            @endif
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

        {{-- Banner window edit --}}
        @if($windowInfo)
            @php $windowBulanLabel = \Carbon\Carbon::create($windowInfo['tahun'], $windowInfo['bulan'], 1)->translatedFormat('F Y'); @endphp
            @if($canEdit)
            <div class="alert alert-success mb-3">
                <i class="ti ti-pencil me-2"></i>
                <strong>Sedang dalam window {{ $windowInfo['reason'] }}.</strong>
                Anda dapat mengedit jadwal bulan <strong>{{ $windowBulanLabel }}</strong>.
            </div>
            @else
            <div class="alert alert-info mb-3">
                <i class="ti ti-info-circle me-2"></i>
                <strong>Mode baca.</strong>
                Saat ini window {{ $windowInfo['reason'] }}.
                Jadwal yang dapat diubah adalah bulan <strong>{{ $windowBulanLabel }}</strong>.
                @if(auth()->user()->hasAnyRole(['admin', 'koordinator']))
                <a href="{{ route('jadwal-security.index', ['bulan' => $windowInfo['bulan'], 'tahun' => $windowInfo['tahun']]) }}" class="alert-link ms-1">Buka bulan tersebut →</a>
                @endif
            </div>
            @endif
        @else
        <div class="alert alert-warning mb-3">
            <i class="ti ti-lock me-2"></i>
            <strong>Di luar window input jadwal.</strong>
            Input jadwal dibuka pada <strong>tanggal 25–akhir bulan</strong> (untuk jadwal bulan depan)
            dan <strong>tanggal 1–5</strong> (untuk revisi jadwal bulan lalu).
        </div>
        @endif

        {{-- Filter Bulan/Tahun --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('jadwal-security.index') }}" class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label mb-1 small">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label mb-1 small">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-auto">
                        @if($isPublished)
                            <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Jadwal bulan ini sudah dipublikasikan</span>
                        @else
                            <span class="badge bg-warning-lt text-warning"><i class="ti ti-clock me-1"></i>Belum dipublikasikan</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Keterangan warna --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-bold me-1 small">Keterangan:</span>
                    @foreach($shifts as $shift)
                        @php
                            $bg = match(strtolower($shift->nama)) {
                                'pagi'  => '#cce5ff', 'siang' => '#fff3cd', 'malam' => '#f8c8dc',
                                default => '#667382',
                            };
                            $tc = match(strtolower($shift->nama)) {
                                'pagi'  => '#004085', 'siang' => '#856404', 'malam' => '#721c47',
                                default => '#fff',
                            };
                        @endphp
                        <span class="badge small" style="background-color:{{ $bg }};color:{{ $tc }};">
                            {{ strtoupper($shift->nama) }}
                            ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                        </span>
                    @endforeach
                    <span class="badge bg-light text-muted small">- = Belum diisi</span>
                </div>
            </div>
        </div>

        {{-- Tabel Kalender --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-calendar me-1"></i>
                    Jadwal Security – {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
                </h3>
                @if($canEdit)
                <div class="card-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopyFromDate">
                        <i class="ti ti-copy me-1"></i>Salin dari tanggal lain
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                @if($pjlps->isEmpty())
                    <div class="empty py-4">
                        <div class="empty-icon"><i class="ti ti-users" style="font-size:2.5rem;"></i></div>
                        <p class="empty-title">Tidak ada PJLP Security</p>
                        <p class="empty-subtitle text-muted">Belum ada PJLP Security yang aktif.</p>
                    </div>
                @else
                    <div class="table-responsive" style="max-height:72vh;overflow:auto;">
                        <table class="table table-bordered table-vcenter table-sm mb-0" id="jadwalTable">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th style="min-width:160px;position:sticky;left:0;background:#fff;z-index:3;">Nama</th>
                                    @foreach($dates as $di)
                                        <th class="text-center small
                                            @if($di['isToday']) bg-info text-white
                                            @elseif($di['isSunday']) bg-danger-lt
                                            @elseif($di['isWeekend']) bg-warning-lt
                                            @endif"
                                            style="min-width:72px;">
                                            {{ $di['day'] }}<br>
                                            <span class="text-muted" style="font-size:0.7rem;">{{ $di['dayName'] }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pjlps as $pjlp)
                                    <tr>
                                        <td style="position:sticky;left:0;background:#fff;z-index:2;">
                                            <div class="fw-semibold small">{{ $pjlp->nama }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ $pjlp->nip ?? '-' }}</div>
                                        </td>
                                        @foreach($dates as $di)
                                            @php
                                                $key    = $pjlp->id . '_' . $di['date']->format('Y-m-d');
                                                $jadwal = $jadwals->get($key);
                                                $shift  = $jadwal?->shift;
                                                $status = $jadwal?->status ?? null;
                                                if ($shift && $status === 'normal') {
                                                    $bg = match(strtolower($shift->nama)) {
                                                        'pagi'  => '#cce5ff', 'siang' => '#fff3cd', 'malam' => '#f8c8dc',
                                                        default => '#667382',
                                                    };
                                                    $tc = match(strtolower($shift->nama)) {
                                                        'pagi'  => '#004085', 'siang' => '#856404', 'malam' => '#721c47',
                                                        default => '#fff',
                                                    };
                                                    $label = strtoupper($shift->nama);
                                                } elseif ($status && $status !== 'normal') {
                                                    [$label, $bg, $tc] = match($status) {
                                                        'libur'           => ['L',     '#e2e8f0', '#4a5568'],
                                                        'libur_hari_raya' => ['HR',    '#fed7aa', '#9a3412'],
                                                        'cuti'            => ['CUTI',  '#dbeafe', '#1e40af'],
                                                        'izin'            => ['IZIN',  '#e9d5ff', '#6b21a8'],
                                                        'sakit'           => ['SAKIT', '#fce7f3', '#9d174d'],
                                                        'alpha'           => ['ALPHA', '#fee2e2', '#991b1b'],
                                                        default           => ['-',     '',        ''],
                                                    };
                                                } else {
                                                    $bg = ''; $tc = ''; $label = '-';
                                                }
                                            @endphp
                                            <td class="text-center p-1
                                                @if($di['isSunday']) bg-danger-lt
                                                @elseif($di['isWeekend']) bg-warning-lt
                                                @endif"
                                                @if($canEdit)
                                                onclick="openModal({{ $pjlp->id }},'{{ $di['date']->format('Y-m-d') }}','{{ addslashes($pjlp->nama) }}','{{ $di['date']->translatedFormat('d M Y') }}')"
                                                style="cursor:pointer;"
                                                @else
                                                style="cursor:default;"
                                                @endif>
                                                <span class="badge jadwal-badge"
                                                      id="badge-{{ $pjlp->id }}-{{ $di['date']->format('Y-m-d') }}"
                                                      style="{{ $bg ? "background-color:{$bg};color:{$tc};" : '' }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Modal Pilih Shift --}}
<div class="modal modal-blur fade" id="shiftModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <strong id="mPjlpName"></strong><br>
                    <small class="text-muted" id="mTanggal"></small>
                </div>
                <input type="hidden" id="mPjlpId">
                <input type="hidden" id="mTanggalVal">

                <div class="mb-3">
                    <label class="form-label small mb-1">Lokasi (opsional)</label>
                    <select class="form-select form-select-sm" id="mLokasi">
                        <option value="">— Tidak ditentukan —</option>
                        @foreach($lokasis as $lok)
                            <option value="{{ $lok->id }}">{{ $lok->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Shift Kerja</label>
                    <div class="d-grid gap-2">
                        @foreach($shifts as $shift)
                            @php
                                $bg = match(strtolower($shift->nama)) {
                                    'pagi'  => '#cce5ff', 'siang' => '#fff3cd', 'malam' => '#f8c8dc',
                                    default => '#667382',
                                };
                                $tc = match(strtolower($shift->nama)) {
                                    'pagi'  => '#004085', 'siang' => '#856404', 'malam' => '#721c47',
                                    default => '#fff',
                                };
                            @endphp
                            <button type="button" class="btn shift-btn fw-semibold"
                                    style="background-color:{{ $bg }};color:{{ $tc }};border-color:{{ $bg }};"
                                    data-shift-id="{{ $shift->id }}"
                                    data-shift-name="{{ $shift->nama }}"
                                    data-bg="{{ $bg }}" data-tc="{{ $tc }}">
                                {{ strtoupper($shift->nama) }}
                                <small class="fw-normal ms-1">({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})</small>
                            </button>
                        @endforeach
                    </div>
                </div>
                <hr class="my-2">
                <div class="mb-2">
                    <label class="form-label small mb-1">Status Non-Kerja</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="libur"           style="background:#e2e8f0;color:#4a5568;">L</button>
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="libur_hari_raya" style="background:#fed7aa;color:#9a3412;">HR</button>
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="cuti"            style="background:#dbeafe;color:#1e40af;">CUTI</button>
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="izin"            style="background:#e9d5ff;color:#6b21a8;">IZIN</button>
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="sakit"           style="background:#fce7f3;color:#9d174d;">SAKIT</button>
                        <button type="button" class="btn btn-sm status-btn fw-semibold" data-status="alpha"           style="background:#fee2e2;color:#991b1b;">ALPHA</button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-1" id="btnHapus">
                    <i class="ti ti-trash me-1"></i>Hapus jadwal hari ini
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Copy dari Tanggal --}}
<div class="modal modal-blur fade" id="copyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-copy me-1"></i>Salin Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal sumber (copy dari)</label>
                    <input type="date" class="form-control" id="copySourceDate"
                           min="{{ \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d') }}"
                           max="{{ \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal tujuan (bisa pilih banyak)</label>
                    <div class="d-flex flex-wrap gap-1" id="copyTargetDates">
                        @foreach($dates as $di)
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" value="{{ $di['date']->format('Y-m-d') }}">
                                <span class="form-check-label small">{{ $di['day'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnDoCopy">
                    <i class="ti ti-copy me-1"></i>Salin
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .jadwal-badge {
        font-size: 0.68rem;
        padding: 0.3em 0.45em;
        min-width: 46px;
        display: inline-block;
    }
    #jadwalTable td:hover { background-color: rgba(0,0,0,.04); }
    thead.sticky-top th { position: sticky; top: 0; z-index: 1; }
</style>
@endpush

@push('scripts')
<script>
let activeModal = null;

function openModal(pjlpId, tanggal, pjlpName, tanggalDisplay) {
    document.getElementById('mPjlpId').value   = pjlpId;
    document.getElementById('mTanggalVal').value = tanggal;
    document.getElementById('mPjlpName').textContent  = pjlpName;
    document.getElementById('mTanggal').textContent   = tanggalDisplay;
    document.getElementById('mLokasi').value  = '';
    activeModal = new bootstrap.Modal(document.getElementById('shiftModal'));
    activeModal.show();
}

// Shift buttons
document.querySelectorAll('.shift-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        saveJadwal({ shift_id: this.dataset.shiftId }, this.dataset.bg, this.dataset.tc);
    });
});

// Status non-kerja buttons
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const bg = this.style.background;
        const tc = this.style.color;
        saveJadwal({ status: this.dataset.status }, bg, tc);
    });
});

// Hapus jadwal
document.getElementById('btnHapus').addEventListener('click', function () {
    if (!confirm('Hapus jadwal hari ini?')) return;
    saveJadwal({}, '', '');
});

function saveJadwal(extra, bgColor, textColor) {
    const pjlpId   = document.getElementById('mPjlpId').value;
    const tanggal  = document.getElementById('mTanggalVal').value;
    const lokasiId = document.getElementById('mLokasi').value;

    const payload = { pjlp_id: pjlpId, tanggal: tanggal, lokasi_id: lokasiId || null, ...extra };

    fetch('{{ route("jadwal-security.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById(`badge-${pjlpId}-${tanggal}`);
            if (badge) {
                badge.textContent = data.display_text;
                badge.style.backgroundColor = data.bg_color;
                badge.style.color = data.text_color;
            }
            if (activeModal) activeModal.hide();
        } else {
            alert(data.message || 'Gagal menyimpan jadwal.');
        }
    })
    .catch(() => alert('Terjadi kesalahan jaringan.'));
}

// Publish
const btnPublish = document.getElementById('btnPublish');
if (btnPublish) btnPublish.addEventListener('click', function () {
    if (!confirm('Publikasikan seluruh jadwal bulan ini agar dapat dilihat oleh PJLP?')) return;
    fetch('{{ route("jadwal-security.publish") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ bulan: {{ $bulan }}, tahun: {{ $tahun }} })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
});

// Copy from date
const btnCopyFromDate = document.getElementById('btnCopyFromDate');
if (btnCopyFromDate) btnCopyFromDate.addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('copyModal')).show();
});

document.getElementById('btnDoCopy').addEventListener('click', function () {
    const sourceDate  = document.getElementById('copySourceDate').value;
    const targetDates = [...document.querySelectorAll('#copyTargetDates input:checked')].map(el => el.value);

    if (!sourceDate) { alert('Pilih tanggal sumber.'); return; }
    if (!targetDates.length) { alert('Pilih minimal 1 tanggal tujuan.'); return; }

    fetch('{{ route("jadwal-security.copy-from-date") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ source_date: sourceDate, target_dates: targetDates })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
@endpush
