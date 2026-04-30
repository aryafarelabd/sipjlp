@extends('layouts.app')

@section('title', 'Edit User')

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
                <h2 class="page-title">Edit User</h2>
                <div class="text-muted mt-1">{{ $user->email }}</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <form action="{{ route('users.update', $user) }}" method="POST" id="formUser">
                    @csrf
                    @method('PUT')

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
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Nama sesuai identitas" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}"
                                       placeholder="contoh: budi.santoso"
                                       autocomplete="off">
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">Huruf kecil, angka, titik, underscore. Digunakan untuk login.</div>
                            </div>

                            <div class="mb-3" id="nipSection" style="{{ in_array(old('role', $user->roles->first()?->name), ['pjlp','danru','koordinator','chief']) ? '' : 'display:none;' }}">
                                <label class="form-label">NIP</label>
                                <input type="text" name="nip" id="nipInput"
                                       class="form-control @error('nip') is-invalid @enderror"
                                       value="{{ old('nip', $user->nip) }}"
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
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="contoh@rsudcipayung.id" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">Digunakan untuk login ke sistem</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Kosongkan jika tidak diubah">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint">Min. 8 karakter</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control"
                                           placeholder="Ulangi password baru">
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

                            @php
                                $cr = old('role', $user->roles->first()?->name);
                                $cu = old('unit', $user->unit?->value);
                                // Determine active path
                                $isPjlp      = in_array($cr, ['pjlp', 'danru']);
                                $isKoord     = in_array($cr, ['koordinator', 'chief']);
                                $isPjlpCs    = $isPjlp && $cu === 'cleaning';
                                $isPjlpSec   = $isPjlp && $cu === 'security';
                                $isDanru     = $cr === 'danru';
                                $isChief     = $cr === 'chief';
                                $isKoordCs   = $isKoord && !$isChief;
                            @endphp

                            <div class="mb-1">
                                <label class="form-label required mb-2">Tipe Akun</label>

                                <div class="role-option-list">

                                    {{-- ── PJLP ── --}}
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
                                        {{-- Level 2: Unit --}}
                                        <div class="role-sub-section" id="pjlpUnitSec" style="{{ $isPjlp ? '' : 'display:none;' }}" onclick="event.stopPropagation()">
                                            <div class="small text-muted mb-2">Pilih unit:</div>
                                            <div class="d-flex flex-wrap gap-2 mb-1">

                                                <label class="sub-role-btn cursor-pointer {{ $isPjlpCs ? 'active' : '' }}" id="btnPjlpCs">
                                                    <input type="radio" name="_g2_pjlp" value="cs" class="d-none g2-pjlp-radio" {{ $isPjlpCs ? 'checked' : '' }}>
                                                    <i class="ti ti-building-hospital me-1"></i> PJLP CS
                                                </label>

                                                <label class="sub-role-btn cursor-pointer {{ $isPjlpSec || $isDanru ? 'active' : '' }}" id="btnPjlpSec">
                                                    <input type="radio" name="_g2_pjlp" value="security" class="d-none g2-pjlp-radio" {{ ($isPjlpSec || $isDanru) ? 'checked' : '' }}>
                                                    <i class="ti ti-shield me-1"></i> PJLP Security
                                                    <i class="ti ti-chevron-right ms-1 text-muted" style="font-size:0.75rem;"></i>
                                                </label>

                                            </div>
                                            {{-- Level 3: Security sub-jabatan --}}
                                            <div id="securitySubSec" style="{{ ($isPjlpSec || $isDanru) ? '' : 'display:none;' }}" class="mt-2 ps-2 border-start border-2 border-green">
                                                <div class="small text-muted mb-2">Pilih jabatan:</div>
                                                <div class="d-flex flex-wrap gap-2">

                                                    <label class="sub-role-btn cursor-pointer {{ ($isPjlpSec && !$isDanru) ? 'active' : '' }}" id="btnAnggota">
                                                        <input type="radio" name="_g3_sec" value="anggota" class="d-none g3-sec-radio" {{ ($isPjlpSec && !$isDanru) ? 'checked' : '' }}>
                                                        <i class="ti ti-user me-1"></i> Anggota
                                                    </label>

                                                    <label class="sub-role-btn cursor-pointer {{ $isDanru ? 'active' : '' }}" id="btnDanru">
                                                        <input type="radio" name="_g3_sec" value="danru" class="d-none g3-sec-radio" {{ $isDanru ? 'checked' : '' }}>
                                                        <i class="ti ti-star me-1"></i> Danru
                                                        <span class="text-muted ms-1" style="font-size:0.72rem;">(Kepala Regu)</span>
                                                    </label>

                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- ── Koordinator / Chief ── --}}
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
                                        {{-- Level 2: Koordinator unit --}}
                                        <div class="role-sub-section" id="koordUnitSec" style="{{ $isKoord ? '' : 'display:none;' }}" onclick="event.stopPropagation()">
                                            <div class="small text-muted mb-2">Pilih unit:</div>
                                            <div class="d-flex flex-wrap gap-2">

                                                <label class="sub-role-btn cursor-pointer {{ $isKoordCs ? 'active' : '' }}" id="btnKoordCs">
                                                    <input type="radio" name="_g2_koord" value="cs" class="d-none g2-koord-radio" {{ $isKoordCs ? 'checked' : '' }}>
                                                    <i class="ti ti-building-hospital me-1"></i> Koordinator CS
                                                </label>

                                                <label class="sub-role-btn cursor-pointer {{ $isChief ? 'active' : '' }}" id="btnChief">
                                                    <input type="radio" name="_g2_koord" value="security" class="d-none g2-koord-radio" {{ $isChief ? 'checked' : '' }}>
                                                    <i class="ti ti-shield-check me-1"></i> Chief Security
                                                </label>

                                            </div>
                                        </div>
                                    </label>

                                    {{-- ── Admin ── --}}
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

                                    {{-- ── Manajemen ── --}}
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

                                {{-- Final hidden inputs --}}
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
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    <span class="fw-medium">Akun Aktif</span>
                                    <span class="form-hint d-block">User dapat login ke sistem</span>
                                </span>
                            </label>
                            @if($user->id === auth()->id())
                            <div class="alert alert-warning mt-2 mb-0 py-2" style="font-size:0.82rem;">
                                <i class="ti ti-alert-triangle me-1"></i>
                                Anda sedang mengedit akun sendiri. Menonaktifkan akun ini akan memblokir login Anda.
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Simpan Perubahan
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

/* Role option list */
.role-option-list { display: flex; flex-direction: column; gap: 0; border: 1px solid var(--tblr-border-color); border-radius: 8px; overflow: hidden; }

.role-option { display: block; margin: 0; border-bottom: 1px solid var(--tblr-border-color); transition: background .12s; }
.role-option:last-child { border-bottom: none; }
.role-option:hover { background: var(--tblr-bg-surface-secondary); }
.role-option.active { background: #f0faf2; }

.role-option-body { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }

.role-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

.role-chevron { transition: transform .2s; font-size: 1rem; }
.role-option.active .role-chevron { transform: rotate(90deg); }

/* Sub-jabatan section */
.role-sub-section { padding: 10px 14px 14px 36px; background: #f6fdf7; border-top: 1px dashed #b8e8c2; }

.sub-role-btn {
    display: inline-flex; align-items: center;
    padding: 5px 12px; border-radius: 20px;
    border: 1.5px solid #cde9d2; background: #fff;
    font-size: 0.82rem; font-weight: 500;
    transition: border-color .12s, background .12s, color .12s;
    white-space: nowrap;
}
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
        const secSub   = document.getElementById('securitySubSec');

        pjlpSec.style.display  = g1 === 'pjlp'        ? '' : 'none';
        koordSec.style.display = g1 === 'koordinator'  ? '' : 'none';
        if (secSub) secSub.style.display = 'none';

        document.querySelectorAll('.g2-pjlp-radio,.g2-koord-radio,.g3-sec-radio').forEach(r => r.checked = false);
        document.querySelectorAll('#pjlpUnitSec .sub-role-btn, #koordUnitSec .sub-role-btn, #securitySubSec .sub-role-btn').forEach(b => b.classList.remove('active'));

        if (g1 !== 'pjlp' && g1 !== 'koordinator') {
            document.getElementById('hiddenRole').value = g1;
            document.getElementById('hiddenUnit').value = '';
        } else {
            document.getElementById('hiddenRole').value = '';
            document.getElementById('hiddenUnit').value = '';
        }
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
        if (radio.value === 'cs') {
            document.getElementById('hiddenRole').value = 'koordinator';
            document.getElementById('hiddenUnit').value = 'cleaning';
        } else {
            document.getElementById('hiddenRole').value = 'chief';
            document.getElementById('hiddenUnit').value = 'security';
        }
    });
});

syncNip();
</script>
@endpush
