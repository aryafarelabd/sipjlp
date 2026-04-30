<?php

namespace App\Http\Controllers;

use App\Enums\StatusCuti;
use App\Enums\StatusLembarKerja;
use App\Enums\UnitType;
use App\Models\Absensi;
use App\Models\BuktiPekerjaanCs;
use App\Models\Cuti;
use App\Models\JadwalShiftCs;
use App\Models\JenisCuti;
use App\Models\BuktiPekerjaanLimbah;
use App\Models\GerakanJumatSehat;
use App\Models\InspeksiHydrant;
use App\Models\InspeksiHydrantIndoor;
use App\Models\LembarKerja;
use App\Models\LogbookBankSampah;
use App\Services\AbsensiSelfieService;
use App\Models\LogbookDekontaminasi;
use App\Models\LogbookHepafilter;
use App\Models\LogbookLimbah;
use App\Models\LogbookB3;
use App\Models\PatrolInspeksi;
use App\Models\PengecekanApar;
use App\Models\PengawasanProyek;
use App\Models\Pjlp;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private AbsensiSelfieService $absensiService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['pjlp', 'danru'])) {
            return $this->pjlpDashboard($user);
        }

        if ($user->hasRole(['koordinator', 'chief'])) {
            return $this->koordinatorDashboard($user);
        }

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('manajemen')) {
            return $this->manajemenDashboard();
        }

        // User tanpa role yang dikenali — arahkan ke profile
        return redirect()->route('profile.edit')
            ->with('error', 'Akun Anda belum dikonfigurasi. Hubungi administrator.');
    }

    private function pjlpDashboard($user)
    {
        $pjlp = $user->pjlp;

        if (!$pjlp) {
            return view('dashboard.pjlp', [
                'pjlp' => null,
                'jadwalShiftHariIni' => null,
                'cutiPending' => collect(),
                'rekapAbsensiBulanIni' => [],
                'sisaCuti' => [],
            ]);
        }

        $today = Carbon::today();
        $month = $today->month;
        $year = $today->year;

        // Jadwal shift hari ini — support Security & CS
        $jadwalInfo = $this->absensiService->getJadwalForPjlp($pjlp, $today);
        $jadwalShiftHariIni = null;
        if ($jadwalInfo['is_kerja'] && $jadwalInfo['shift']) {
            // Buat objek pseudo agar view lama tetap kompatibel
            $jadwalShiftHariIni = (object) [
                'status'       => 'normal',
                'shift'        => $jadwalInfo['shift'],
                'status_label' => 'Kerja',
                'status_color' => 'success',
            ];
        } elseif (isset($jadwalInfo['jadwal']) && $jadwalInfo['jadwal']) {
            // Non-kerja day (libur/cuti/etc) — hanya CS punya status_label
            $jadwalShiftHariIni = $jadwalInfo['jadwal'];
        }

        $cutiPending = $pjlp->cuti()->pending()->latest()->take(5)->get();

        // Rekap absensi bulan ini dari tabel absensi.
        $absensiBulanIni = Absensi::where('pjlp_id', $pjlp->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        $rekapAbsensiBulanIni = [
            'hari_masuk'  => $absensiBulanIni->whereIn('status.value', ['hadir', 'terlambat'])->count(),
            'total_alpha' => $absensiBulanIni->where('status.value', 'alpha')->count(),
            'total_telat' => $absensiBulanIni->where('status.value', 'terlambat')->sum('menit_terlambat'),
        ];

        // Hitung sisa cuti tahun ini per jenis cuti (exclude Cuti Melahirkan dan Cuti Besar)
        $jenisCutiList = JenisCuti::active()
            ->whereNotIn('nama', ['Cuti Melahirkan', 'Cuti Besar'])
            ->whereNotNull('max_hari')
            ->get();
        $sisaCuti = [];
        foreach ($jenisCutiList as $jenis) {
            $terpakai = Cuti::forPjlp($pjlp->id)
                ->where('jenis_cuti_id', $jenis->id)
                ->where('status', StatusCuti::DISETUJUI)
                ->whereYear('tgl_mulai', $year)
                ->sum('jumlah_hari');

            $sisaCuti[] = [
                'jenis' => $jenis->nama,
                'max_hari' => $jenis->max_hari,
                'terpakai' => $terpakai,
                'sisa' => $jenis->max_hari - $terpakai,
            ];
        }

        // Jumat Sehat bulan ini
        $jumatSehatCount = GerakanJumatSehat::where('pjlp_id', $pjlp->id)
            ->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

        // Unit-specific stats bulan ini
        $unitStats = [];
        if ($pjlp->unit === UnitType::CLEANING) {
            $unitStats = [
                'logbook_limbah'      => LogbookLimbah::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'logbook_b3'          => LogbookB3::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'logbook_hepafilter'  => LogbookHepafilter::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'logbook_dekont'      => LogbookDekontaminasi::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'logbook_bank_sampah' => LogbookBankSampah::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
            ];
        } elseif ($pjlp->unit === UnitType::SECURITY) {
            $unitStats = [
                'patrol'          => PatrolInspeksi::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'hydrant_outdoor' => InspeksiHydrant::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'hydrant_indoor'  => InspeksiHydrantIndoor::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'apar'            => PengecekanApar::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
                'proyek'          => PengawasanProyek::where('pjlp_id', $pjlp->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count(),
            ];
        }

        return view('dashboard.pjlp', compact(
            'pjlp',
            'jadwalShiftHariIni',
            'cutiPending',
            'rekapAbsensiBulanIni',
            'sisaCuti',
            'jumatSehatCount',
            'unitStats'
        ));
    }

    private function koordinatorDashboard($user)
    {
        $unit = $user->unit;

        $pjlpQuery = Pjlp::query()->forKoordinator($user);
        $totalPjlp = $pjlpQuery->count();
        $pjlpAktif = $pjlpQuery->active()->count();

        $today = Carbon::today();

        // Cuti pending unit
        $cutiPending = Cuti::whereHas('pjlp', function ($q) use ($user) {
            $q->forKoordinator($user);
        })->pending()->count();

        // Bukti Pekerjaan CS pending validasi
        $buktiPendingCount = BuktiPekerjaanCs::whereHas('pjlp', function ($q) use ($user) {
            $q->forKoordinator($user);
        })
        ->where('is_completed', true)
        ->where('is_validated', false)
        ->where('is_rejected', false)
        ->count();

        // Absensi hari ini unit.
        $pjlpIds = Pjlp::active()->forKoordinator($user)->pluck('id');
        $absensiHariIniQuery = Absensi::whereIn('pjlp_id', $pjlpIds)->whereDate('tanggal', $today);
        $absensiMasukHariIni = (clone $absensiHariIniQuery)->whereNotNull('jam_masuk')->count();
        $absensiAlphaHariIni = (clone $absensiHariIniQuery)->where('status', 'alpha')->count();
        $absensiHariIni      = $absensiMasukHariIni;

        // Recent cuti requests
        $recentCuti = Cuti::whereHas('pjlp', function ($q) use ($user) {
            $q->forKoordinator($user);
        })->with('pjlp', 'jenisCuti')->latest()->take(5)->get();

        // Recent bukti pekerjaan CS yang menunggu validasi
        $recentBuktiPending = BuktiPekerjaanCs::whereHas('pjlp', function ($q) use ($user) {
            $q->forKoordinator($user);
        })
        ->where('is_completed', true)
        ->where('is_validated', false)
        ->where('is_rejected', false)
        ->with(['pjlp', 'jadwalBulanan.area'])
        ->latest()
        ->take(5)
        ->get();

        return view('dashboard.koordinator', compact(
            'unit',
            'totalPjlp',
            'pjlpAktif',
            'cutiPending',
            'buktiPendingCount',
            'absensiHariIni',
            'absensiMasukHariIni',
            'absensiAlphaHariIni',
            'recentCuti',
            'recentBuktiPending'
        ));
    }

    private function adminDashboard()
    {
        $totalPjlp = Pjlp::count();
        $pjlpSecurity = Pjlp::unit('security')->count();
        $pjlpCleaning = Pjlp::unit('cleaning')->count();
        $pjlpAktif = Pjlp::active()->count();

        $cutiPending = Cuti::pending()->count();
        $buktiCsPending = BuktiPekerjaanCs::where('is_completed', true)
            ->where('is_validated', false)->where('is_rejected', false)->count();

        $today = Carbon::today();
        $absensiMasukHariIni = Absensi::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count();
        $absensiAlphaHariIni = Absensi::whereDate('tanggal', $today)->where('status', 'alpha')->count();
        $absensiHariIni      = $absensiMasukHariIni;

        // Recent activities
        $recentCuti = Cuti::with('pjlp', 'jenisCuti')->latest()->take(5)->get();
        $recentBuktiCs = BuktiPekerjaanCs::with(['pjlp', 'jadwalBulanan.area'])
            ->latest()->take(5)->get();

        return view('dashboard.admin', compact(
            'totalPjlp',
            'pjlpSecurity',
            'pjlpCleaning',
            'pjlpAktif',
            'cutiPending',
            'buktiCsPending',
            'absensiHariIni',
            'absensiMasukHariIni',
            'absensiAlphaHariIni',
            'recentCuti',
            'recentBuktiCs'
        ));
    }

    private function manajemenDashboard()
    {
        $totalPjlp = Pjlp::count();
        $pjlpSecurity = Pjlp::unit('security')->count();
        $pjlpCleaning = Pjlp::unit('cleaning')->count();

        $today = Carbon::today();
        $month = $today->month;
        $year = $today->year;

        // Rekap absensi bulan ini.
        $absensiBulanIni = Absensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $rekapAbsensi = [
            'total_hadir'  => ($absensiBulanIni['hadir'] ?? 0) + ($absensiBulanIni['terlambat'] ?? 0),
            'total_alpha'  => $absensiBulanIni['alpha'] ?? 0,
            'total_izin'   => ($absensiBulanIni['izin'] ?? 0) + ($absensiBulanIni['cuti'] ?? 0),
            'total_telat'  => Absensi::whereYear('tanggal', $year)
                                ->whereMonth('tanggal', $month)
                                ->where('status', 'terlambat')
                                ->sum('menit_terlambat'),
        ];

        // Rekap cuti bulan ini
        $rekapCuti = Cuti::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('dashboard.manajemen', compact(
            'totalPjlp',
            'pjlpSecurity',
            'pjlpCleaning',
            'rekapAbsensi',
            'rekapCuti'
        ));
    }
}
