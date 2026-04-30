<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('images/logo-icon.png') }}" alt="SiPJLP" height="36" class="me-2">
                <div>
                    <span class="text-white fw-bold">SiPJLP</span>
                    <small class="text-muted d-block" style="font-size: 0.65rem;">Sistem Informasi PJLP</small>
                </div>
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=206bc4&color=fff)"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">Profil</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            @php
                $user      = auth()->user();
                $userPjlp  = \App\Models\Pjlp::where('user_id', $user->id)->first();
                $userUnit  = $user->unit; // unit di tabel users (all/cleaning/security)

                $isAdmin           = $user->hasRole('admin');
                $isKoordinator     = $user->hasRole('koordinator');
                $isDanru           = $user->hasRole('danru');
                $isChief           = $user->hasRole('chief');
                $isAdminOrKoordinator = $isAdmin || $isKoordinator || $user->hasRole('manajemen');

                // Unit koordinator: null atau 'all' berarti lihat semua
                $unitValue = $userUnit?->value ?? 'all';
                $showCs       = $isAdmin || ($isKoordinator && in_array($unitValue, ['all', 'cleaning']));
                // Chief/koordinator/admin lihat rekap security; danru tetap seperti anggota security.
                $showSecurity = $isAdmin || $isChief
                    || ($isKoordinator && in_array($unitValue, ['all', 'security']));

                // PJLP
                $isCleaningPjlp  = $userPjlp && $userPjlp->unit === \App\Enums\UnitType::CLEANING;
                $isSecurityPjlp  = $userPjlp && $userPjlp->unit === \App\Enums\UnitType::SECURITY;
            @endphp

            <ul class="navbar-nav pt-lg-3">

                {{-- ============================================================
                     DASHBOARD
                ============================================================ --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-home"></i>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>

                {{-- ============================================================
                     ABSEN — hanya muncul untuk PJLP (punya profil PJLP)
                ============================================================ --}}
                @if($userPjlp)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('absen.index') ? 'active' : '' }}" href="{{ route('absen.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-fingerprint"></i>
                        </span>
                        <span class="nav-link-title">Rekap Absen Saya</span>
                    </a>
                </li>
                @endif

                {{-- ============================================================
                     TELEGRAM — hanya koordinator & admin
                ============================================================ --}}
                @if(auth()->user()->hasAnyRole(['koordinator', 'admin']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('telegram.*') ? 'active' : '' }}" href="{{ route('telegram.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-brand-telegram"></i>
                        </span>
                        <span class="nav-link-title">
                            Notifikasi Telegram
                            @if(!auth()->user()->telegram_chat_id)
                                <span class="badge bg-warning ms-1" style="font-size:0.6rem;padding:2px 5px;">Belum terhubung</span>
                            @endif
                        </span>
                    </a>
                </li>
                @endif

                {{-- ============================================================
                     SECTION: PEGAWAI
                     Terlihat oleh: koordinator (unit), admin (semua)
                ============================================================ --}}
                @canany(['pjlp.view-unit', 'pjlp.view-all'])
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Pegawai
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pjlp.*') ? 'active' : '' }}" href="{{ route('pjlp.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users"></i>
                        </span>
                        <span class="nav-link-title">Data PJLP</span>
                    </a>
                </li>
                @endcanany

                {{-- ============================================================
                     SECTION: ABSENSI
                     Terlihat oleh: koordinator, admin
                ============================================================ --}}
                @if(!$isDanru && !$user->hasRole('pjlp'))
                @canany(['absensi.view-unit', 'absensi.view-all'])
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Absensi
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('absensi.rekap') ? 'active' : '' }}" href="{{ route('absensi.rekap') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-fingerprint"></i>
                        </span>
                        <span class="nav-link-title">Rekap Absensi</span>
                    </a>
                </li>
                @endcanany
                @endif

                {{-- ============================================================
                     CUTI — section header hanya untuk koordinator/admin (bisa approve)
                     PJLP hanya lihat item flat tanpa header
                ============================================================ --}}
                @canany(['cuti.create', 'cuti.view-unit', 'cuti.view-all', 'cuti.approve'])
                @if($isAdminOrKoordinator)
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Cuti
                    </div>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cuti.index', 'cuti.create') || (request()->routeIs('cuti.show') && request('from') !== 'validasi') ? 'active' : '' }}" href="{{ route('cuti.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-plane-departure"></i>
                        </span>
                        <span class="nav-link-title">{{ $isAdminOrKoordinator ? 'Pengajuan Cuti' : 'Cuti Saya' }}</span>
                    </a>
                </li>
                @can('cuti.approve')
                @php
                    $pendingCutiValidasi = \App\Models\Cuti::query();
                    if ($user->hasRole('admin')) {
                        $pendingCutiValidasi->pending();
                    } elseif ($user->hasRole('danru')) {
                        $pendingCutiValidasi
                            ->where('status', \App\Enums\StatusCuti::MENUNGGU_DANRU)
                            ->where('danru_id', $userPjlp?->id);
                    } elseif ($user->hasRole('chief')) {
                        $pendingCutiValidasi
                            ->where('status', \App\Enums\StatusCuti::MENUNGGU_CHIEF)
                            ->whereHas('pjlp', fn($q) => $q->where('unit', \App\Enums\UnitType::SECURITY));
                    } elseif ($user->hasRole('koordinator')) {
                        $pendingCutiValidasi
                            ->whereIn('status', [\App\Enums\StatusCuti::MENUNGGU, \App\Enums\StatusCuti::MENUNGGU_KOORDINATOR])
                            ->whereHas('pjlp', function ($q) use ($user) {
                                if ($user->unit && $user->unit->value !== 'all') {
                                    $q->where('unit', $user->unit);
                                }
                            });
                    } else {
                        $pendingCutiValidasi->whereRaw('1 = 0');
                    }
                    $pendingCuti = $pendingCutiValidasi->count();
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cuti.validasi') || (request()->routeIs('cuti.show') && request('from') === 'validasi') ? 'active' : '' }}" href="{{ route('cuti.validasi') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-clipboard-check"></i>
                        </span>
                        <span class="nav-link-title">Validasi Cuti</span>
                        @if($pendingCuti > 0)
                        <span class="badge badge-sm bg-red ms-auto">{{ $pendingCuti }}</span>
                        @endif
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ============================================================
                     CLEANING SERVICE
                     Tampil untuk: admin, koordinator unit cleaning/all, PJLP cleaning
                ============================================================ --}}
                @if($showCs || $isCleaningPjlp)
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Cleaning Service
                    </div>
                </li>
                @endif

                @if($showCs)
                    @php
                        $pendingLkCs = \App\Models\LembarKerjaCs::byStatus('submitted')->count();
                        $isJadwalCsActive = request()->routeIs('jadwal-shift-cs.*') || request()->routeIs('jadwal-kerja-cs-bulanan.*');
                    @endphp

                    @can('jadwal-cs.manage')
                    <li class="nav-item">
                        <a class="nav-link {{ $isJadwalCsActive ? 'active' : '' }}" href="{{ route('jadwal-shift-cs.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-calendar-stats"></i>
                            </span>
                            <span class="nav-link-title">Jadwal CS</span>
                        </a>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('lembar-kerja-cs.*') ? 'active' : '' }}" href="{{ route('lembar-kerja-cs.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-file-text"></i>
                            </span>
                            <span class="nav-link-title">Lembar Kerja CS</span>
                            @if($pendingLkCs > 0)
                            <span class="badge badge-sm bg-warning ms-auto">{{ $pendingLkCs }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Logbook Limbah: koordinator lihat rekap --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logbook-limbah.*') ? 'active' : '' }}" href="{{ route('logbook-limbah.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-file-invoice"></i>
                            </span>
                            <span class="nav-link-title">Rekap Logbook Limbah Domestik</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logbook-b3.*') ? 'active' : '' }}" href="{{ route('logbook-b3.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-biohazard"></i>
                            </span>
                            <span class="nav-link-title">Rekap Logbook Limbah B3</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logbook-hepafilter.*') ? 'active' : '' }}" href="{{ route('logbook-hepafilter.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-filter"></i>
                            </span>
                            <span class="nav-link-title">Rekap Cleaning Hepafilter</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logbook-dekontaminasi.*') ? 'active' : '' }}" href="{{ route('logbook-dekontaminasi.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-wind"></i>
                            </span>
                            <span class="nav-link-title">Rekap Dekontaminasi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logbook-bank-sampah.*') ? 'active' : '' }}" href="{{ route('logbook-bank-sampah.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-recycle"></i>
                            </span>
                            <span class="nav-link-title">Rekap Bank Sampah</span>
                        </a>
                    </li>
                    @if($isKoordinator)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('gerakan-jumat-sehat.*') ? 'active' : '' }}" href="{{ route('gerakan-jumat-sehat.rekap') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-heartbeat"></i>
                            </span>
                            <span class="nav-link-title">Rekap Jumat Sehat</span>
                        </a>
                    </li>
                    @endif

                @elseif($isCleaningPjlp)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('lembar-kerja-cs.*') ? 'active' : '' }}" href="{{ route('lembar-kerja-cs.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-file-text"></i>
                        </span>
                        <span class="nav-link-title">Lembar Kerja Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logbook-limbah.*') ? 'active' : '' }}" href="{{ route('logbook-limbah.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-file-invoice"></i>
                        </span>
                        <span class="nav-link-title">Logbook Limbah Domestik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logbook-b3.*') ? 'active' : '' }}" href="{{ route('logbook-b3.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-biohazard"></i>
                        </span>
                        <span class="nav-link-title">Logbook Limbah B3</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logbook-hepafilter.*') ? 'active' : '' }}" href="{{ route('logbook-hepafilter.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-filter"></i>
                        </span>
                        <span class="nav-link-title">Cleaning Hepafilter</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logbook-dekontaminasi.*') ? 'active' : '' }}" href="{{ route('logbook-dekontaminasi.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-wind"></i>
                        </span>
                        <span class="nav-link-title">Dekontaminasi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logbook-bank-sampah.*') ? 'active' : '' }}" href="{{ route('logbook-bank-sampah.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-recycle"></i>
                        </span>
                        <span class="nav-link-title">Logbook Bank Sampah</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gerakan-jumat-sehat.*') ? 'active' : '' }}" href="{{ route('gerakan-jumat-sehat.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-heartbeat"></i>
                        </span>
                        <span class="nav-link-title">Jumat Sehat</span>
                    </a>
                </li>
                @endif

                {{-- ============================================================
                     SECURITY
                     Tampil untuk: admin, koordinator unit security/all, PJLP security
                     (modul masih on progress — placeholder)
                ============================================================ --}}
                @if($showSecurity || $isSecurityPjlp)
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Security
                    </div>
                </li>
                @if($isAdmin)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('jadwal-security.*') ? 'active' : '' }}" href="{{ route('jadwal-security.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-calendar-stats"></i>
                        </span>
                        <span class="nav-link-title">Jadwal Security</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('patrol-inspeksi.*') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('patrol-inspeksi.rekap') : route('patrol-inspeksi.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-shield-check"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap Security Patrol' : 'Laporan Patrol' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pengawasan-proyek.*') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('pengawasan-proyek.rekap') : route('pengawasan-proyek.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-building-factory-2"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap Pengawasan Proyek' : 'Pengawasan Proyek' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pengecekan-apar.*') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('pengecekan-apar.rekap') : route('pengecekan-apar.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-fire-extinguisher"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap APAR & APAB' : 'Pengecekan APAR & APAB' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inspeksi-hydrant.index') || request()->routeIs('inspeksi-hydrant.store') || request()->routeIs('inspeksi-hydrant.show') || request()->routeIs('inspeksi-hydrant.rekap') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('inspeksi-hydrant.rekap') : route('inspeksi-hydrant.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-flame"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap Hydrant Outdoor' : 'Hydrant Outdoor' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inspeksi-hydrant-indoor.*') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('inspeksi-hydrant-indoor.rekap') : route('inspeksi-hydrant-indoor.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-flame-off"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap Hydrant Indoor' : 'Hydrant Indoor' }}
                        </span>
                    </a>
                </li>
                {{-- Laporan Parkir --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('laporan-parkir.*') ? 'active' : '' }}"
                       href="{{ $showSecurity ? route('laporan-parkir.rekap') : route('laporan-parkir.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-parking"></i>
                        </span>
                        <span class="nav-link-title">
                            {{ $showSecurity ? 'Rekap Parkir Menginap' : 'Laporan Parkir' }}
                        </span>
                    </a>
                </li>

                @if($isSecurityPjlp)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gerakan-jumat-sehat.*') ? 'active' : '' }}" href="{{ route('gerakan-jumat-sehat.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-heartbeat"></i>
                        </span>
                        <span class="nav-link-title">Jumat Sehat</span>
                    </a>
                </li>
                @endif
                @if($isChief || $isKoordinator)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gerakan-jumat-sehat.rekap') ? 'active' : '' }}" href="{{ route('gerakan-jumat-sehat.rekap') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-heartbeat"></i>
                        </span>
                        <span class="nav-link-title">Rekap Jumat Sehat</span>
                    </a>
                </li>
                @endif
                @endif

                {{-- ============================================================
                     K3 — Laporan Kecelakaan Kerja
                     Terlihat oleh: semua user (form), rekap untuk admin/koordinator
                ============================================================ --}}
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        K3
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('laporan-kecelakaan.index') || request()->routeIs('laporan-kecelakaan.show') ? 'active' : '' }}"
                       href="{{ route('laporan-kecelakaan.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                        <span class="nav-link-title">Laporan Kecelakaan</span>
                    </a>
                </li>
                @can('laporan.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('laporan-kecelakaan.rekap') ? 'active' : '' }}"
                       href="{{ route('laporan-kecelakaan.rekap') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-report-medical"></i>
                        </span>
                        <span class="nav-link-title">Rekap K3</span>
                    </a>
                </li>
                @endcan

                {{-- ============================================================
                     SECTION: LAPORAN
                     Terlihat oleh: koordinator, admin, manajemen
                ============================================================ --}}
                @can('laporan.view')
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Laporan
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('laporan.cuti') ? 'active' : '' }}" href="{{ route('laporan.cuti') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-report-analytics"></i>
                        </span>
                        <span class="nav-link-title">Rekap Cuti</span>
                    </a>
                </li>
                @endcan

                {{-- ============================================================
                     SECTION: ADMINISTRASI
                     Terlihat oleh: admin saja
                ============================================================ --}}
                @canany(['master.manage', 'user.manage', 'audit-log.view', 'jadwal.manage', 'jadwal-cs.manage'])
                <li class="nav-item">
                    <div class="nav-link-title text-uppercase text-muted small px-3 pt-3 pb-1" style="font-size:0.7rem;letter-spacing:.08em;">
                        Administrasi
                    </div>
                </li>
                @can('master.manage')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('master.*') || request()->routeIs('master-pekerjaan-cs.*') ? 'active' : '' }}"
                       href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-database"></i>
                        </span>
                        <span class="nav-link-title">Master Data</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('master.*') || request()->routeIs('master-pekerjaan-cs.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('master.shift.*') ? 'active' : '' }}" href="{{ route('master.shift.index') }}">
                            <i class="ti ti-clock me-2"></i> Shift
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('master.jenis-cuti.*') ? 'active' : '' }}" href="{{ route('master.jenis-cuti.index') }}">
                            <i class="ti ti-plane me-2"></i> Jenis Cuti
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('master.lokasi.*') ? 'active' : '' }}" href="{{ route('master.lokasi.index') }}">
                            <i class="ti ti-map-pin me-2"></i> Lokasi Security
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('master.area-cs.*') ? 'active' : '' }}" href="{{ route('master.area-cs.index') }}">
                            <i class="ti ti-map me-2"></i> Area CS
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('master-pekerjaan-cs.*') ? 'active' : '' }}" href="{{ route('master-pekerjaan-cs.index') }}">
                            <i class="ti ti-tools me-2"></i> Pekerjaan CS
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('master.kegiatan-lk-cs.*') ? 'active' : '' }}" href="{{ route('master.kegiatan-lk-cs.index') }}">
                            <i class="ti ti-list-check me-2"></i> Kegiatan LK CS
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item {{ request()->routeIs('master.app-settings.*') ? 'active' : '' }}" href="{{ route('master.app-settings.index') }}">
                            <i class="ti ti-settings me-2"></i> Pengaturan Sistem
                        </a>
                    </div>
                </li>
                @endcan
                @cannot('master.manage')
                @if($isAdmin && $showSecurity)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.lokasi.*') ? 'active' : '' }}" href="{{ route('master.lokasi.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-map-pin"></i>
                        </span>
                        <span class="nav-link-title">Lokasi Security</span>
                    </a>
                </li>
                @endif
                @if($showCs && auth()->user()->can('jadwal-cs.manage'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.area-cs.*') ? 'active' : '' }}" href="{{ route('master.area-cs.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-map"></i>
                        </span>
                        <span class="nav-link-title">Area CS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.kegiatan-lk-cs.*') ? 'active' : '' }}" href="{{ route('master.kegiatan-lk-cs.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-list-check"></i>
                        </span>
                        <span class="nav-link-title">Kegiatan LK CS</span>
                    </a>
                </li>
                @endif
                @endcannot
                @can('user.manage')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-user-cog"></i>
                        </span>
                        <span class="nav-link-title">Manajemen User</span>
                    </a>
                </li>
                @endcan
                @can('audit-log.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit-log.*') ? 'active' : '' }}" href="{{ route('audit-log.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-history"></i>
                        </span>
                        <span class="nav-link-title">Audit Log</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ============================================================
                     PROFIL & LOGOUT
                ============================================================ --}}
                <li class="nav-item mt-auto">
                    <hr class="dropdown-divider my-2">
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <span class="avatar avatar-xs" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=206bc4&color=fff)"></span>
                        </span>
                        <span class="nav-link-title">{{ $user->name }}</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="ti ti-user me-2"></i> Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</aside>
