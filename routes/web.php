<?php

use App\Http\Controllers\AbsensiSelfieController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LogbookB3Controller;
use App\Http\Controllers\LogbookHepafilterController;
use App\Http\Controllers\LogbookDekontaminasiController;
use App\Http\Controllers\LogbookBankSampahController;
use App\Http\Controllers\PatrolInspeksiController;
use App\Http\Controllers\InspeksiHydrantController;
use App\Http\Controllers\InspeksiHydrantIndoorController;
use App\Http\Controllers\PengawasanProyekController;
use App\Http\Controllers\PengecekanAparController;
use App\Http\Controllers\LaporanKecelakaanController;
use App\Http\Controllers\GerakanJumatSehatController;
use App\Http\Controllers\LogbookLimbahController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LembarKerjaController;
use App\Http\Controllers\LembarKerjaCsController;
use App\Http\Controllers\JadwalKerjaCsBulananController;
use App\Http\Controllers\JadwalSecurityController;
use App\Http\Controllers\JadwalShiftCsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Master\AppSettingController;
use App\Http\Controllers\Master\JenisCutiController;
use App\Http\Controllers\Master\KegiatanLkCsController;
use App\Http\Controllers\Master\LokasiController;
use App\Http\Controllers\Master\ShiftController;
use App\Http\Controllers\MasterAreaCsController;
use App\Http\Controllers\MasterPekerjaanCsController;
use App\Http\Controllers\PjlpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Telegram — hanya koordinator dan admin
    Route::middleware('role:koordinator|admin')->group(function () {
        Route::get('/telegram', [TelegramController::class, 'index'])->name('telegram.index');
        Route::post('/telegram/poll', [TelegramController::class, 'pollConnect'])->name('telegram.poll');
        Route::delete('/telegram/disconnect', [TelegramController::class, 'disconnect'])->name('telegram.disconnect');
        Route::post('/telegram/test', [TelegramController::class, 'testKirim'])->name('telegram.test');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // PJLP Management
    Route::resource('pjlp', PjlpController::class);

    // Absensi Selfie (mobile) — hanya PJLP
    Route::middleware('can:absensi.view-self')->group(function () {
        Route::get('/absen', [AbsensiSelfieController::class, 'showAbsenPage'])->name('absen.index');
        Route::post('/absen/masuk', [AbsensiSelfieController::class, 'absenMasuk'])->name('absen.masuk')->middleware('throttle:5,1');
        Route::post('/absen/pulang', [AbsensiSelfieController::class, 'absenPulang'])->name('absen.pulang')->middleware('throttle:5,1');
        Route::get('/jadwal-saya', [AbsensiSelfieController::class, 'jadwalSaya'])->name('jadwal-saya.index');
    });
    Route::get('/absensi/rekap', [AbsensiSelfieController::class, 'rekapAbsensi'])
        ->name('absensi.rekap');
    Route::get('/absensi/rekap/export', [AbsensiSelfieController::class, 'exportRekap'])
        ->name('absensi.rekap.export');
    Route::post('/absensi/koreksi', [AbsensiSelfieController::class, 'simpanKoreksi'])
        ->name('absensi.koreksi');

    // Cuti
    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::get('/cuti/create', [CutiController::class, 'create'])->name('cuti.create');
    Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
    Route::get('/cuti/{cuti}', [CutiController::class, 'show'])->name('cuti.show');
    Route::post('/cuti/{cuti}/approve', [CutiController::class, 'approve'])->name('cuti.approve');
    Route::post('/cuti/{cuti}/reject', [CutiController::class, 'reject'])->name('cuti.reject');

    // Lembar Kerja Security (on progress — sidebar masih disabled)
    Route::get('/lembar-kerja', [LembarKerjaController::class, 'index'])->name('lembar-kerja.index');
    Route::get('/lembar-kerja/create', [LembarKerjaController::class, 'create'])->name('lembar-kerja.create');
    Route::post('/lembar-kerja', [LembarKerjaController::class, 'store'])->name('lembar-kerja.store');
    Route::get('/lembar-kerja/{lembarKerja}', [LembarKerjaController::class, 'show'])->name('lembar-kerja.show');
    Route::get('/lembar-kerja/{lembarKerja}/edit', [LembarKerjaController::class, 'edit'])->name('lembar-kerja.edit');
    Route::put('/lembar-kerja/{lembarKerja}', [LembarKerjaController::class, 'update'])->name('lembar-kerja.update');
    Route::post('/lembar-kerja/{lembarKerja}/submit', [LembarKerjaController::class, 'submit'])->name('lembar-kerja.submit');
    Route::post('/lembar-kerja/{lembarKerja}/validate', [LembarKerjaController::class, 'validateLK'])->name('lembar-kerja.validate');
    Route::post('/lembar-kerja/{lembarKerja}/reject', [LembarKerjaController::class, 'reject'])->name('lembar-kerja.reject');
    Route::post('/lembar-kerja/{lembarKerja}/detail', [LembarKerjaController::class, 'addDetail'])->name('lembar-kerja.detail.store');
    Route::delete('/lembar-kerja/detail/{detail}', [LembarKerjaController::class, 'deleteDetail'])->name('lembar-kerja.detail.destroy');

    // Jadwal Shift Security
    Route::prefix('jadwal-security')->name('jadwal-security.')->middleware('can:jadwal.manage')->group(function () {
        Route::get('/', [JadwalSecurityController::class, 'index'])->name('index');
        Route::post('/update', [JadwalSecurityController::class, 'update'])->name('update');
        Route::post('/publish', [JadwalSecurityController::class, 'publish'])->name('publish');
        Route::post('/copy-from-date', [JadwalSecurityController::class, 'copyFromDate'])->name('copy-from-date');
    });

    // Jadwal Shift CS
    Route::prefix('jadwal-shift-cs')->name('jadwal-shift-cs.')->middleware('can:jadwal-cs.manage')->group(function () {
        Route::get('/', [JadwalShiftCsController::class, 'index'])->name('index');
        Route::post('/update', [JadwalShiftCsController::class, 'update'])->name('update');
        Route::post('/publish', [JadwalShiftCsController::class, 'publish'])->name('publish');
        Route::post('/bulk-update', [JadwalShiftCsController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/copy-from-date', [JadwalShiftCsController::class, 'copyFromDate'])->name('copy-from-date');
    });

    // Jadwal Kerja CS Bulanan
    Route::prefix('jadwal-kerja-cs-bulanan')->name('jadwal-kerja-cs-bulanan.')->middleware('can:jadwal-cs.manage')->group(function () {
        Route::get('/', [JadwalKerjaCsBulananController::class, 'index'])->name('index');
        Route::get('/create', [JadwalKerjaCsBulananController::class, 'create'])->name('create');
        Route::post('/', [JadwalKerjaCsBulananController::class, 'store'])->name('store');
        Route::post('/copy', [JadwalKerjaCsBulananController::class, 'copy'])->name('copy');
        Route::post('/bulk-copy', [JadwalKerjaCsBulananController::class, 'bulkCopy'])->name('bulk-copy');
        Route::get('/{jadwalKerjaCsBulanan}/edit', [JadwalKerjaCsBulananController::class, 'edit'])->name('edit');
        Route::put('/{jadwalKerjaCsBulanan}', [JadwalKerjaCsBulananController::class, 'update'])->name('update');
        Route::delete('/{jadwalKerjaCsBulanan}', [JadwalKerjaCsBulananController::class, 'destroy'])->name('destroy');
    });

    // Master Pekerjaan CS
    Route::prefix('master-pekerjaan-cs')->name('master-pekerjaan-cs.')->middleware('can:cs.pekerjaan.manage')->group(function () {
        Route::get('/', [MasterPekerjaanCsController::class, 'index'])->name('index');
        Route::get('/create', [MasterPekerjaanCsController::class, 'create'])->name('create');
        Route::post('/', [MasterPekerjaanCsController::class, 'store'])->name('store');
        Route::get('/{masterPekerjaanC}/edit', [MasterPekerjaanCsController::class, 'edit'])->name('edit');
        Route::put('/{masterPekerjaanC}', [MasterPekerjaanCsController::class, 'update'])->name('update');
        Route::delete('/{masterPekerjaanC}', [MasterPekerjaanCsController::class, 'destroy'])->name('destroy');
        Route::patch('/{masterPekerjaanC}/toggle-status', [MasterPekerjaanCsController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Lembar Kerja CS (Cleaning Service)
    Route::prefix('lembar-kerja-cs')->name('lembar-kerja-cs.')->group(function () {
        Route::get('/', [LembarKerjaCsController::class, 'index'])->name('index');
        Route::post('/', [LembarKerjaCsController::class, 'store'])->name('store');
        Route::get('/rekap', [LembarKerjaCsController::class, 'rekap'])->name('rekap');
        Route::get('/{lembarKerjaC}', [LembarKerjaCsController::class, 'show'])->name('show');
        Route::post('/{lembarKerjaC}/validate', [LembarKerjaCsController::class, 'validateLk'])->name('validate');
        Route::post('/{lembarKerjaC}/reject', [LembarKerjaCsController::class, 'rejectLk'])->name('reject');
        Route::delete('/{lembarKerjaC}', [LembarKerjaCsController::class, 'destroy'])->name('destroy');
    });

    // =========================================================
    // ROUTE INPUT PJLP — dibatasi jadwal shift (restrict.shift)
    // Admin, koordinator, danru, chief bypass (lihat rekap)
    // PJLP tanpa jadwal hari ini → redirect dashboard dengan error
    // =========================================================
    Route::middleware('restrict.shift')->group(function () {
        // CS — logbook store
        Route::post('/logbook-limbah', [LogbookLimbahController::class, 'store'])->name('logbook-limbah.store');
        Route::post('/logbook-b3', [LogbookB3Controller::class, 'store'])->name('logbook-b3.store');
        Route::post('/logbook-hepafilter', [LogbookHepafilterController::class, 'store'])->name('logbook-hepafilter.store');
        Route::post('/logbook-dekontaminasi', [LogbookDekontaminasiController::class, 'store'])->name('logbook-dekontaminasi.store');
        Route::post('/logbook-bank-sampah', [LogbookBankSampahController::class, 'store'])->name('logbook-bank-sampah.store');

        // Security — laporan store
        Route::post('/patrol-inspeksi', [PatrolInspeksiController::class, 'store'])->name('patrol-inspeksi.store');
        Route::post('/inspeksi-hydrant', [InspeksiHydrantController::class, 'store'])->name('inspeksi-hydrant.store');
        Route::post('/inspeksi-hydrant-indoor', [InspeksiHydrantIndoorController::class, 'store'])->name('inspeksi-hydrant-indoor.store');
        Route::post('/pengecekan-apar', [PengecekanAparController::class, 'store'])->name('pengecekan-apar.store');
        Route::post('/pengawasan-proyek', [PengawasanProyekController::class, 'store'])->name('pengawasan-proyek.store');
    });

    // Logbook Limbah Domestik/Kompos
    Route::prefix('logbook-limbah')->name('logbook-limbah.')->group(function () {
        Route::get('/', [LogbookLimbahController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [LogbookLimbahController::class, 'index'])->name('rekap');
    });

    // Gerakan Jumat Sehat
    Route::prefix('gerakan-jumat-sehat')->name('gerakan-jumat-sehat.')->group(function () {
        Route::get('/', [GerakanJumatSehatController::class, 'index'])->name('index');
        Route::post('/', [GerakanJumatSehatController::class, 'store'])->name('store');
        Route::get('/rekap', [GerakanJumatSehatController::class, 'rekap'])->name('rekap');
    });

    // Laporan Kecelakaan Kerja, Insiden & Ketidaksesuaian (K3)
    Route::prefix('laporan-kecelakaan')->name('laporan-kecelakaan.')->group(function () {
        Route::get('/', [LaporanKecelakaanController::class, 'index'])->name('index');
        Route::post('/', [LaporanKecelakaanController::class, 'store'])->name('store');
        Route::get('/rekap', [LaporanKecelakaanController::class, 'rekap'])->name('rekap')
             ->middleware('can:laporan.view');
        Route::get('/{laporanKecelakaan}', [LaporanKecelakaanController::class, 'show'])->name('show');
    });

    // Pengecekan APAR & APAB
    Route::prefix('pengecekan-apar')->name('pengecekan-apar.')->group(function () {
        Route::get('/', [PengecekanAparController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [PengecekanAparController::class, 'rekap'])->name('rekap');
        Route::get('/{pengecekanApar}', [PengecekanAparController::class, 'show'])->name('show');
    });

    // Pengawasan Proyek
    Route::prefix('pengawasan-proyek')->name('pengawasan-proyek.')->group(function () {
        Route::get('/', [PengawasanProyekController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [PengawasanProyekController::class, 'rekap'])->name('rekap');
        Route::get('/{pengawasanProyek}', [PengawasanProyekController::class, 'show'])->name('show');
    });

    // Inspeksi Hydrant Indoor
    Route::prefix('inspeksi-hydrant-indoor')->name('inspeksi-hydrant-indoor.')->group(function () {
        Route::get('/', [InspeksiHydrantIndoorController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [InspeksiHydrantIndoorController::class, 'rekap'])->name('rekap');
        Route::get('/{inspeksiHydrantIndoor}', [InspeksiHydrantIndoorController::class, 'show'])->name('show');
    });

    // Inspeksi Hydrant Outdoor
    Route::prefix('inspeksi-hydrant')->name('inspeksi-hydrant.')->group(function () {
        Route::get('/', [InspeksiHydrantController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [InspeksiHydrantController::class, 'index'])->name('rekap');
        Route::get('/{inspeksiHydrant}', [InspeksiHydrantController::class, 'show'])->name('show');
    });

    // Security Patrol Inspeksi
    Route::prefix('patrol-inspeksi')->name('patrol-inspeksi.')->group(function () {
        Route::get('/', [PatrolInspeksiController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [PatrolInspeksiController::class, 'index'])->name('rekap');
        Route::get('/{patrolInspeksi}', [PatrolInspeksiController::class, 'show'])->name('show');
    });

    // Logbook Bank Sampah
    Route::prefix('logbook-bank-sampah')->name('logbook-bank-sampah.')->group(function () {
        Route::get('/', [LogbookBankSampahController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [LogbookBankSampahController::class, 'index'])->name('rekap');
    });

    // Logbook Dekontaminasi Udara
    Route::prefix('logbook-dekontaminasi')->name('logbook-dekontaminasi.')->group(function () {
        Route::get('/', [LogbookDekontaminasiController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [LogbookDekontaminasiController::class, 'index'])->name('rekap');
    });

    // Logbook Cleaning Hepafilter
    Route::prefix('logbook-hepafilter')->name('logbook-hepafilter.')->group(function () {
        Route::get('/', [LogbookHepafilterController::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [LogbookHepafilterController::class, 'index'])->name('rekap');
    });

    // Logbook Limbah B3
    Route::prefix('logbook-b3')->name('logbook-b3.')->group(function () {
        Route::get('/', [LogbookB3Controller::class, 'index'])->name('index')->middleware('restrict.shift');
        Route::get('/rekap', [LogbookB3Controller::class, 'index'])->name('rekap');
    });

    // Export (Excel / PDF) — semua modul rekap
    Route::prefix('export')->name('export.')->middleware('role:koordinator|admin|manajemen|danru|chief')->group(function () {
        Route::get('/lembar-kerja-cs',       [ExportController::class, 'lembarKerjaCs'])->name('lembar-kerja-cs');
        Route::get('/logbook-limbah',        [ExportController::class, 'logbookLimbah'])->name('logbook-limbah');
        Route::get('/logbook-b3',            [ExportController::class, 'logbookB3'])->name('logbook-b3');
        Route::get('/logbook-hepafilter',    [ExportController::class, 'logbookHepafilter'])->name('logbook-hepafilter');
        Route::get('/logbook-dekontaminasi', [ExportController::class, 'logbookDekontaminasi'])->name('logbook-dekontaminasi');
        Route::get('/logbook-bank-sampah',   [ExportController::class, 'logbookBankSampah'])->name('logbook-bank-sampah');
        Route::get('/patrol-inspeksi',       [ExportController::class, 'patrolInspeksi'])->name('patrol-inspeksi');
        Route::get('/inspeksi-hydrant',      [ExportController::class, 'inspeksiHydrant'])->name('inspeksi-hydrant');
        Route::get('/inspeksi-hydrant-indoor',[ExportController::class, 'inspeksiHydrantIndoor'])->name('inspeksi-hydrant-indoor');
        Route::get('/pengecekan-apar',       [ExportController::class, 'pengecekanApar'])->name('pengecekan-apar');
        Route::get('/pengawasan-proyek',     [ExportController::class, 'pengawasanProyek'])->name('pengawasan-proyek');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->middleware('can:laporan.view')->group(function () {
        Route::get('/absensi', [LaporanController::class, 'absensi'])->name('absensi');
        Route::get('/absensi/export', [LaporanController::class, 'exportAbsensi'])->name('absensi.export');
        Route::get('/cuti', [LaporanController::class, 'cuti'])->name('cuti');
        Route::get('/cuti/export', [LaporanController::class, 'exportCuti'])->name('cuti.export');
    });

    // Master Data (admin only)
    Route::prefix('master')->name('master.')->middleware('can:master.manage')->group(function () {
        Route::get('/app-settings', [AppSettingController::class, 'index'])->name('app-settings.index');
        Route::post('/app-settings/jadwal-cs-window', [AppSettingController::class, 'updateJadwalCsWindow'])->name('app-settings.jadwal-cs-window');
        Route::resource('shift', ShiftController::class);
        Route::resource('jenis-cuti', JenisCutiController::class);
        Route::prefix('kegiatan-lk-cs')->name('kegiatan-lk-cs.')->group(function () {
            Route::get('/', [KegiatanLkCsController::class, 'index'])->name('index');
            Route::post('/', [KegiatanLkCsController::class, 'store'])->name('store');
            Route::put('/{kegiatanLkC}', [KegiatanLkCsController::class, 'update'])->name('update');
            Route::delete('/{kegiatanLkC}', [KegiatanLkCsController::class, 'destroy'])->name('destroy');
        });
    });

    // Lokasi — admin dan koordinator Security (jadwal.manage)
    Route::prefix('master/lokasi')->name('master.lokasi.')->middleware('can:jadwal.manage')->group(function () {
        Route::resource('/', LokasiController::class)->parameters(['' => 'lokasi']);
    });

    // Area CS — admin dan koordinator CS (jadwal-cs.manage)
    Route::prefix('master/area-cs')->name('master.area-cs.')->middleware('can:jadwal-cs.manage')->group(function () {
        Route::resource('/', MasterAreaCsController::class)->parameters(['' => 'masterAreaCs']);
    });

    // User Management
    Route::resource('users', UserController::class)->middleware('can:user.manage');

    // Audit Log
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index')->middleware('can:audit-log.view');
});

// Forgot password placeholder
Route::get('/forgot-password', function () {
    return back()->with('error', 'Fitur lupa password belum tersedia. Hubungi Administrator.');
})->name('password.request');
