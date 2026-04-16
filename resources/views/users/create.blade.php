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

                            {{-- Role selector cards --}}
                            <div class="mb-3">
                                <label class="form-label required">Role</label>
                                <div class="row g-2" id="roleCards">
                                    @php
                                        $roleInfo = [
                                            'pjlp'        => ['color' => 'green',  'icon' => 'ti-id-badge',    'desc' => 'Absen selfie, lembar kerja, cuti'],
                                            'koordinator' => ['color' => 'blue',   'icon' => 'ti-user-check',  'desc' => 'Kelola jadwal & rekap unit'],
                                            'admin'       => ['color' => 'red',    'icon' => 'ti-settings',    'desc' => 'Akses penuh ke semua fitur'],
                                            'manajemen'   => ['color' => 'purple', 'icon' => 'ti-chart-bar',   'desc' => 'Lihat laporan & statistik'],
                                        ];
                                    @endphp
                                    @foreach($roles as $role)
                                    @php $info = $roleInfo[$role->name] ?? ['color' => 'secondary', 'icon' => 'ti-user', 'desc' => '']; @endphp
                                    <div class="col-6 col-md-3">
                                        <label class="role-card d-block cursor-pointer">
                                            <input type="radio" name="role" value="{{ $role->name }}"
                                                   class="d-none role-radio"
                                                   {{ old('role') == $role->name ? 'checked' : '' }} required>
                                            <div class="card card-sm text-center p-2 role-card-inner border-2"
                                                 data-color="{{ $info['color'] }}">
                                                <div class="avatar avatar-sm bg-{{ $info['color'] }}-lt mx-auto mb-1 mt-1">
                                                    <i class="ti {{ $info['icon'] }}"></i>
                                                </div>
                                                <div class="fw-bold small">{{ ucfirst($role->name) }}</div>
                                                <div class="text-muted" style="font-size:0.68rem;line-height:1.3">{{ $info['desc'] }}</div>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Unit — hanya muncul untuk pjlp & koordinator --}}
                            <div id="unitSection" class="mb-3" style="display:none;">
                                <label class="form-label" id="unitLabel">Unit</label>
                                <div class="row g-2">
                                    @foreach(\App\Enums\UnitType::cases() as $unit)
                                    <div class="col-auto">
                                        <label class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="unit" value="{{ $unit->value }}"
                                                   {{ old('unit') == $unit->value ? 'checked' : '' }}>
                                            <span class="form-check-label fw-medium">{{ $unit->label() }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                    <div class="col-auto" id="unitNoneOption">
                                        <label class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="unit" value=""
                                                   {{ old('unit') === null ? 'checked' : '' }}>
                                            <span class="form-check-label text-muted">Tidak Ada</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-hint" id="unitHint"></div>
                            </div>

                            {{-- Info box hak akses per role --}}
                            <div id="roleInfoBox" class="alert alert-info d-none" style="font-size:0.85rem;">
                                <i class="ti ti-info-circle me-1"></i>
                                <span id="roleInfoText"></span>
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
.role-card-inner { transition: border-color .15s, background .15s; border-color: transparent !important; }
.role-radio:checked + .role-card-inner { border-color: var(--tblr-primary) !important; background: var(--tblr-primary-lt); }
.role-radio[value="pjlp"]:checked + .role-card-inner        { border-color: #2fb344 !important; background: #d1f0d8; }
.role-radio[value="koordinator"]:checked + .role-card-inner { border-color: #206bc4 !important; background: #dce8f8; }
.role-radio[value="admin"]:checked + .role-card-inner       { border-color: #d63939 !important; background: #fce8e8; }
.role-radio[value="manajemen"]:checked + .role-card-inner   { border-color: #ae3ec9 !important; background: #f3d9f8; }
.cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
const roleInfo = {
    pjlp:        { showUnit: true,  showNip: true,  unitRequired: false, hint: 'Unit hanya informasi tambahan untuk PJLP. Data unit utama ada di profil PJLP.', info: 'PJLP dapat absen selfie, isi lembar kerja, dan mengajukan cuti.' },
    koordinator: { showUnit: true,  showNip: false, unitRequired: true,  hint: 'Koordinator hanya bisa melihat PJLP di unitnya.', info: 'Koordinator mengelola jadwal, melihat rekap absensi, dan memvalidasi pekerjaan untuk unitnya.' },
    admin:       { showUnit: false, showNip: false, unitRequired: false, hint: '', info: 'Admin memiliki akses penuh ke seluruh fitur sistem termasuk manajemen user dan master data.' },
    manajemen:   { showUnit: false, showNip: false, unitRequired: false, hint: '', info: 'Manajemen hanya bisa melihat laporan dan statistik, tidak bisa mengubah data.' },
};

function onRoleChange(role) {
    const cfg = roleInfo[role] || { showUnit: false, showNip: false, unitRequired: false, hint: '', info: '' };
    const unitSection = document.getElementById('unitSection');
    const unitHint    = document.getElementById('unitHint');
    const unitLabel   = document.getElementById('unitLabel');
    const infoBox     = document.getElementById('roleInfoBox');
    const infoText    = document.getElementById('roleInfoText');
    const nipSection  = document.getElementById('nipSection');
    const nipInput    = document.getElementById('nipInput');

    unitSection.style.display = cfg.showUnit ? '' : 'none';
    unitHint.textContent = cfg.hint;
    unitLabel.textContent = cfg.unitRequired ? 'Unit *' : 'Unit';

    nipSection.style.display = cfg.showNip ? '' : 'none';
    nipInput.required = cfg.showNip;

    if (cfg.info) {
        infoText.textContent = cfg.info;
        infoBox.classList.remove('d-none');
    } else {
        infoBox.classList.add('d-none');
    }
}

document.querySelectorAll('.role-radio').forEach(radio => {
    radio.addEventListener('change', () => onRoleChange(radio.value));
});

// Init on load (e.g. validation error redirect with old value)
const checkedRole = document.querySelector('.role-radio:checked');
if (checkedRole) onRoleChange(checkedRole.value);
</script>
@endpush
