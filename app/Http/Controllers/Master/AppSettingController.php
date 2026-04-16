<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        $jadwalCsOverride = AppSetting::get('jadwal_window_override', 'auto');

        return view('master.app-settings.index', compact('jadwalCsOverride'));
    }

    public function updateJadwalCsWindow(Request $request)
    {
        $request->validate([
            'override' => 'required|in:auto,open,closed',
        ]);

        $old = AppSetting::get('jadwal_window_override', 'auto');
        AppSetting::set('jadwal_window_override', $request->override);

        AuditLog::log("Ubah jadwal_window_override: {$old} → {$request->override}");

        $label = match($request->override) {
            'open'   => 'Paksa Buka',
            'closed' => 'Paksa Tutup',
            default  => 'Otomatis',
        };

        return back()->with('success', "Window input jadwal CS berhasil diubah ke: {$label}.");
    }
}
