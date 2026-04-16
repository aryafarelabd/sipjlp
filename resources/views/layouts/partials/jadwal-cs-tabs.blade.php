<div class="mb-3">
    <ul class="nav nav-tabs nav-fill">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('jadwal-shift-cs.*') ? 'active' : '' }}"
               href="{{ route('jadwal-shift-cs.index') }}">
                <i class="ti ti-users me-2"></i>Jadwal Shift PJLP
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('jadwal-kerja-cs-bulanan.*') ? 'active' : '' }}"
               href="{{ route('jadwal-kerja-cs-bulanan.index') }}">
                <i class="ti ti-clipboard-list me-2"></i>Pekerjaan Harian
            </a>
        </li>
    </ul>
</div>
