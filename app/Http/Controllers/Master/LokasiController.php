<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasi = Lokasi::latest()->paginate(15);
        return view('master.lokasi.index', compact('lokasi'));
    }

    public function create()
    {
        return view('master.lokasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'kode'      => 'required|string|max:50',
            'gedung'    => 'nullable|string|max:100',
            'lantai'    => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $lokasi = Lokasi::create($validated);

        AuditLog::log('Menambah lokasi', $lokasi, null, $lokasi->toArray());

        return redirect()->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Lokasi $lokasi)
    {
        return view('master.lokasi.edit', compact('lokasi'));
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50',
            'gedung' => 'nullable|string|max:100',
            'lantai' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $dataLama = $lokasi->toArray();
        $lokasi->update($validated);

        AuditLog::log('Update lokasi', $lokasi, $dataLama, $lokasi->fresh()->toArray());

        return redirect()->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Lokasi $lokasi)
    {
        $dataLama = $lokasi->toArray();
        $lokasi->delete();

        AuditLog::log('Menghapus lokasi', null, $dataLama, null);

        return redirect()->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
