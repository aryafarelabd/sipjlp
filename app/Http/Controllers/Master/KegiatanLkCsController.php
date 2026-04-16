<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterKegiatanLkCs;
use Illuminate\Http\Request;

class KegiatanLkCsController extends Controller
{


    public function index()
    {
        $periodik  = MasterKegiatanLkCs::where('tipe', 'periodik')->ordered()->get();
        $extraJob  = MasterKegiatanLkCs::where('tipe', 'extra_job')->ordered()->get();

        return view('master.kegiatan-lk-cs.index', compact('periodik', 'extraJob'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'   => 'required|string|max:200',
            'tipe'   => 'required|in:periodik,extra_job',
            'urutan' => 'nullable|integer|min:0',
        ]);

        MasterKegiatanLkCs::create([
            'nama'      => $validated['nama'],
            'tipe'      => $validated['tipe'],
            'urutan'    => $validated['urutan'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, MasterKegiatanLkCs $kegiatanLkC)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:200',
            'urutan'    => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $kegiatanLkC->update([
            'nama'      => $validated['nama'],
            'urutan'    => $validated['urutan'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(MasterKegiatanLkCs $kegiatanLkC)
    {
        $kegiatanLkC->delete();
        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }
}
