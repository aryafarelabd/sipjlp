@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <h2 class="page-title">Tambah User Baru</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <form action="{{ route('users.store') }}" method="POST" id="formUser">
                    @csrf

                    {{-- Informasi Akun --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2 text-primary"></i>Informasi Akun</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Nama sesuai identitas" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username') }}"
                                       placeholder="contoh: budi.santoso"
                                       autocomplete="off">
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">Huruf kecil, angka, titik, underscore. Digunakan untuk login.</div>
                            </div>

                            <div class="mb-3" id="nipSection" style="display:none;">
                                <label class="form-label">NIP <span class="text-red" id="nipRequired">*</span></label>
                                <input type="text" name="nip" id="nipInput"
                                       class="form-control @error('nip') is-invalid @enderror"
                                       value="{{ old('nip') }}"
                                       placeholder="Contoh: 198001012005011001"
                                       inputmode="numeric" pattern="[0-9]*"
                                       maxlength="30">
                                @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">Hanya angka, digunakan sebagai alternatif login</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="contoh@rsudcipayung.id" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">Digunakan untuk login ke sistem</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label required">Password</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 8 karakter" required>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label required">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control"
                                           placeholder="Ulangi password" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hak Akses --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield-check me-2 text-blue"></i>Hak Akses</h3>
                        </div>
                        <div class="card-body">

                            {{-- Role --}}
                            @php
                                $cr = old('role');
                                $cu = old('unit');
                                $isPjlp    = in_array($cr, ['pjlp', 'danru']);
                                $isKoord    = in_array($cr, ['koordinator', 'chief', 'pj_cs']);
                                $isPjlpCs   = $isPjlp && $cu === 'cleaning';
                                $isPjlpSec  = $isPjlp && $cu === 'security';
                                $isDanru    = $cr === 'danru';
                                $isChief    = $cr === 'chief';
                                $isPjCs     = $cr === 'pj_cs';
                                $isKoordCs  = $isKoord && $cu === 'cleaning';
                                $isKoordSec = $cr === 'koordinator' && $cu === 'security';
                            @endphp
                            <div class="mb-1">
                                <label class="form-label required mb-2">Tipe Akun</label>

                                <div class="role-option-list">

                                    <label class="role-option cursor-pointer {{ $isPjlp ? 'active' : '' }}">
                                        <input type="radio" name="_g1" value="pjlp" class="d-none g1-radio" {{ $isPjlp ? 'checked' : '' }} required>
                                        <div class="role-option-body">
                                            <span class="role-dot bg-green"></span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">PJLP <span class="badge bg-green-lt text-green ms-1" style="font-size:0.65rem;">Pegawai Lapangan</span></div>
                                                <div class="text-muted small">Absen finger, lembar kerja harian, pengajuan cuti</div>
                                            </div>
                                            <i class="ti ti-chevron-right role-chevron text-muted"></i>
                                        </div>
                                        <div class="role-sub-section" id="pjlpUnitSec" style="{{ $isPjlp ? '' : 'display:none;' }}" onclick="event.stopPropagation()">
                                            <div class="small text-muted mb-2">Pilih unit:</div>
                                            <div class="d-flex flex-wrap gap-2 mb-1">
                                                <label class="sub-role-btn cursor-pointer {{ $isPjlpCs ? 'active' : '' }}">
                                                    <input type="radio" name="_g2_pjlp" value="cs" class="d-none g2-pjlp-radio" {{ $isPjlpCs ? 'checked' : '' }}>
                                                    <i class="ti ti-building-hospital me-1"></i> PJLP CS
                                                </label>
                                                <label class="sub-role-btn cursor-pointer {{ ($isPjlpSec || $isDanru) ? 'active' : '' }}">
                                                    <input type="radio" name="_g2_pjlp" value="security" class="d-none g2-pjlp-radio" {{ ($isPjlpSec || $isDanru) ? 'checked' : '' }}>
                                                    <i class="ti ti-shield me-1"></i> PJLP Security
                                                    <i class="ti ti-chevron-right ms-1 text-muted" style="font-size:0.75rem;"></i>
                                                </label>
                                            </div>
                                            <div id="securitySubSec" style="{{ ($isPjlpSec || $isDanru) ? '' : 'display:none;' }}" class="mt-2 ps-2 border-start border-2 border-green">
                                                <div class="small text-muted mb-2">Pilih jabatan:</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label class="sub-role-btn cursor-pointer {{ ($isPjlpSec && !$isDanru) ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_sec" value="anggota" class="d-none g3-sec-radio" {{ ($isPjlpSec && !$isDanru) ? 'checked' : '' }}>
                                                        <i class="ti ti-user me-1"></i> Anggota
                                                    </label>
                                                    <label class="sub-role-btn cursor-pointer {{ $isDanru ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_sec" value="danru" class="d-none g3-sec-radio" {{ $isDanru ? 'checked' : '' }}>
                                                        <i class="ti ti-star me-1"></i> Danru
                                                        <span class="text-muted ms-1" style="font-size:0.72rem;">(Kepala Regu)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="role-option cursor-pointer {{ $isKoord ? 'active' : '' }}">
                                        <input type="radio" name="_g1" value="koordinator" class="d-none g1-radio" {{ $isKoord ? 'checked' : '' }}>
                                        <div class="role-option-body">
                                            <span class="role-dot bg-blue"></span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">Koordinator / Chief</div>
                                                <div class="text-muted small">Kelola jadwal, validasi cuti & pekerjaan, rekap absensi unit</div>
                                            </div>
                                            <i class="ti ti-chevron-right role-chevron text-muted"></i>
                                        </div>
                                        <div class="role-sub-section" id="koordUnitSec" style="{{ $isKoord ? '' : 'display:none;' }}" onclick="event.stopPropagation()">
                                            <div class="small text-muted mb-2">Pilih unit:</div>
                                            <div class="d-flex flex-wrap gap-2 mb-1">
                                                <label class="sub-role-btn cursor-pointer {{ $isKoordCs ? 'active' : '' }}">
                                                    <input type="radio" name="_g2_koord" value="cs" class="d-none g2-koord-radio" {{ $isKoordCs ? 'checked' : '' }}>
                                                    <i class="ti ti-building-hospital me-1"></i> Unit CS
                                                    <i class="ti ti-chevron-right ms-1 text-muted" style="font-size:0.75rem;"></i>
                                                </label>
                                                <label class="sub-role-btn cursor-pointer {{ ($isChief || $isKoordSec) ? 'active' : '' }}">
                                                    <input type="radio" name="_g2_koord" value="security" class="d-none g2-koord-radio" {{ ($isChief || $isKoordSec) ? 'checked' : '' }}>
                                                    <i class="ti ti-shield me-1"></i> Unit Security
                                                    <i class="ti ti-chevron-right ms-1 text-muted" style="font-size:0.75rem;"></i>
                                                </label>
                                            </div>
                                            <div id="secKoordSubSec" style="{{ ($isChief || $isKoordSec) ? '' : 'display:none;' }}" class="mt-2 ps-2 border-start border-2 border-blue">
                                                <div class="small text-muted mb-2">Pilih jabatan:</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label class="sub-role-btn cursor-pointer {{ $isKoordSec ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_sec_koord" value="koordinator" class="d-none g3-sec-koord-radio" {{ $isKoordSec ? 'checked' : '' }}>
                                                        <i class="ti ti-user-check me-1"></i> Koordinator Security
                                                    </label>
                                                    <label class="sub-role-btn cursor-pointer {{ $isChief ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_sec_koord" value="chief" class="d-none g3-sec-koord-radio" {{ $isChief ? 'checked' : '' }}>
                                                        <i class="ti ti-shield-check me-1"></i> Chief Security
                                                        <span class="text-muted ms-1" style="font-size:0.72rem;">(Kepala Lapangan)</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="csSubSec" style="{{ $isKoordCs ? '' : 'display:none;' }}" class="mt-2 ps-2 border-start border-2 border-blue">
                                                <div class="small text-muted mb-2">Pilih jabatan:</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label class="sub-role-btn cursor-pointer {{ ($isKoordCs && !$isPjCs) ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_cs" value="koordinator" class="d-none g3-cs-radio" {{ ($isKoordCs && !$isPjCs) ? 'checked' : '' }}>
                                                        <i class="ti ti-user-check me-1"></i> Koordinator CS
                                                    </label>
                                                    <label class="sub-role-btn cursor-pointer {{ $isPjCs ? 'active' : '' }}">
                                                        <input type="radio" name="_g3_cs" value="pj_cs" class="d-none g3-cs-radio" {{ $isPjCs ? 'checked' : '' }}>
                                                        <i class="ti ti-star me-1"></i> PJ CS
                                                        <span class="text-muted ms-1" style="font-size:0.72rem;">(Penanggung Jawab)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="role-option cursor-pointer {{ $cr === 'admin' ? 'active' : '' }}">
                                        <input type="radio" name="_g1" value="admin" class="d-none g1-radio" {{ $cr === 'admin' ? 'checked' : '' }}>
                                        <div class="role-option-body">
                                            <span class="role-dot bg-red"></span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">Admin</div>
                                                <div class="text-muted small">Akses penuh ke seluruh fitur dan manajemen user</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="role-option cursor-pointer {{ $cr === 'manajemen' ? 'active' : '' }}">
                                        <input type="radio" name="_g1" value="manajemen" class="d-none g1-radio" {{ $cr === 'manajemen' ? 'checked' : '' }}>
                                        <div class="role-option-body">
                                            <span class="role-dot bg-purple"></span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">Manajemen</div>
                                                <div class="text-muted small">Hanya lihat laporan dan statistik, tidak bisa ubah data</div>
                                            </div>
                                        </div>
                                    </label>

                                </div>

                                <input type="hidden" name="role" id="hiddenRole" value="{{ $cr }}">
                                <input type="hidden" name="unit" id="hiddenUnit" value="{{ $cu }}">

                                @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <span class="form-check-label">
                                    <span class="fw-medium">Akun Aktif</span>
                                    <span class="form-hint d-block">User dapat login ke sistem</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Buat User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.cursor-pointer { cursor: pointer; }
.role-option-list { display: flex; flex-direction: column; gap: 0; border: 1px solid var(--tblr-border-color); border-radius: 8px; overflow: hidden; }
.role-option { display: block; margin: 0; border-bottom: 1px solid var(--tblr-border-color); transition: background .12s; }
.role-option:last-child { border-bottom: none; }
.role-option:hover { background: var(--tblr-bg-surface-secondary); }
.role-option.active { background: #f0faf2; }
.role-option-body { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }
.role-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.role-chevron { transition: transform .2s; font-size: 1rem; }
.role-option.active .role-chevron { transform: rotate(90deg); }
.role-sub-section { padding: 10px 14px 14px 36px; background: #f6fdf7; border-top: 1px dashed #b8e8c2; }
.sub-role-btn { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 20px; border: 1.5px solid #cde9d2; background: #fff; font-size: 0.82rem; font-weight: 500; transition: border-color .12s, background .12s, color .12s; white-space: nowrap; }
.sub-role-btn:hover { border-color: #2fb344; background: #edfaef; }
.sub-role-btn.active { border-color: #2fb344 !important; background: #d4f0da !important; color: #1a7a2e; font-weight: 600; }
</style>
@endpush

@push('scripts')
<script>
function setG1Active(g1El) {
    document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
    g1El.classList.add('active');
}
function setSubActive(container, activeEl) {
    container.querySelectorAll('.sub-role-btn').forEach(b => b.classList.remove('active'));
    activeEl.classList.add('active');
}
function syncNip() {
    const g1 = document.querySelector('.g1-radio:checked')?.value;
    const nip = document.getElementById('nipSection');
    if (nip) nip.style.display = (g1 === 'admin' || g1 === 'manajemen') ? 'none' : '';
}

document.querySelectorAll('.g1-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        const g1 = radio.value;
        setG1Active(radio.closest('.role-option'));
        const pjlpSec  = document.getElementById('pjlpUnitSec');
        const koordSec = document.getElementById('koordUnitSec');
        const secSub        = document.getElementById('securitySubSec');
        const csSub         = document.getElementById('csSubSec');
        const secKoordSub   = document.getElementById('secKoordSubSec');
        pjlpSec.style.display  = g1 === 'pjlp'       ? '' : 'none';
        koordSec.style.display = g1 === 'koordinator' ? '' : 'none';
        if (secSub)      secSub.style.display      = 'none';
        if (csSub)       csSub.style.display       = 'none';
        if (secKoordSub) secKoordSub.style.display = 'none';
        document.querySelectorAll('.g2-pjlp-radio,.g2-koord-radio,.g3-sec-radio,.g3-cs-radio,.g3-sec-koord-radio').forEach(r => r.checked = false);
        document.querySelectorAll('#pjlpUnitSec .sub-role-btn,#koordUnitSec .sub-role-btn,#securitySubSec .sub-role-btn,#csSubSec .sub-role-btn,#secKoordSubSec .sub-role-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('hiddenRole').value = (g1 !== 'pjlp' && g1 !== 'koordinator') ? g1 : '';
        document.getElementById('hiddenUnit').value = '';
        syncNip();
    });
});

document.querySelectorAll('.g2-pjlp-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        const unit = radio.value;
        setSubActive(document.getElementById('pjlpUnitSec'), radio.closest('.sub-role-btn'));
        const secSub = document.getElementById('securitySubSec');
        if (unit === 'cs') {
            secSub.style.display = 'none';
            document.querySelectorAll('.g3-sec-radio').forEach(r => r.checked = false);
            document.querySelectorAll('#securitySubSec .sub-role-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('hiddenRole').value = 'pjlp';
            document.getElementById('hiddenUnit').value = 'cleaning';
        } else {
            secSub.style.display = '';
            const g3 = document.querySelector('.g3-sec-radio:checked');
            if (!g3) {
                const first = document.querySelector('.g3-sec-radio');
                if (first) { first.checked = true; first.closest('.sub-role-btn').classList.add('active'); }
            }
            const g3val = document.querySelector('.g3-sec-radio:checked')?.value ?? 'anggota';
            document.getElementById('hiddenRole').value = g3val === 'danru' ? 'danru' : 'pjlp';
            document.getElementById('hiddenUnit').value = 'security';
        }
    });
});

document.querySelectorAll('.g3-sec-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        setSubActive(document.getElementById('securitySubSec'), radio.closest('.sub-role-btn'));
        document.getElementById('hiddenRole').value = radio.value === 'danru' ? 'danru' : 'pjlp';
        document.getElementById('hiddenUnit').value = 'security';
    });
});

document.querySelectorAll('.g2-koord-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        setSubActive(document.getElementById('koordUnitSec'), radio.closest('.sub-role-btn'));
        const csSub        = document.getElementById('csSubSec');
        const secKoordSub  = document.getElementById('secKoordSubSec');
        if (radio.value === 'cs') {
            csSub.style.display       = '';
            secKoordSub.style.display = 'none';
            document.querySelectorAll('.g3-sec-koord-radio').forEach(r => r.checked = false);
            document.querySelectorAll('#secKoordSubSec .sub-role-btn').forEach(b => b.classList.remove('active'));
            const g3cs = document.querySelector('.g3-cs-radio:checked');
            if (!g3cs) {
                const first = document.querySelector('.g3-cs-radio');
                if (first) { first.checked = true; first.closest('.sub-role-btn').classList.add('active'); }
            }
            const g3csVal = document.querySelector('.g3-cs-radio:checked')?.value ?? 'koordinator';
            document.getElementById('hiddenRole').value = g3csVal;
            document.getElementById('hiddenUnit').value = 'cleaning';
        } else {
            csSub.style.display       = 'none';
            secKoordSub.style.display = '';
            document.querySelectorAll('.g3-cs-radio').forEach(r => r.checked = false);
            document.querySelectorAll('#csSubSec .sub-role-btn').forEach(b => b.classList.remove('active'));
            const g3sk = document.querySelector('.g3-sec-koord-radio:checked');
            if (!g3sk) {
                const first = document.querySelector('.g3-sec-koord-radio');
                if (first) { first.checked = true; first.closest('.sub-role-btn').classList.add('active'); }
            }
            const g3skVal = document.querySelector('.g3-sec-koord-radio:checked')?.value ?? 'chief';
            document.getElementById('hiddenRole').value = g3skVal;
            document.getElementById('hiddenUnit').value = 'security';
        }
    });
});

document.querySelectorAll('.g3-cs-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        setSubActive(document.getElementById('csSubSec'), radio.closest('.sub-role-btn'));
        document.getElementById('hiddenRole').value = radio.value;
        document.getElementById('hiddenUnit').value = 'cleaning';
    });
});

document.querySelectorAll('.g3-sec-koord-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        setSubActive(document.getElementById('secKoordSubSec'), radio.closest('.sub-role-btn'));
        document.getElementById('hiddenRole').value = radio.value;
        document.getElementById('hiddenUnit').value = 'security';
    });
});

syncNip();
</script>
@endpush
