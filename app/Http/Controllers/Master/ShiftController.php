<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->paginate(15);
        return view('master.shift.index', compact('shifts'));
    }

    public function create()
    {
        return view('master.shift.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:100',
            'jam_mulai'           => 'required|date_format:H:i',
            'bi'                  => 'nullable|date_format:H:i',
            'ai'                  => 'nullable|date_format:H:i',
            'jam_selesai'         => 'required|date_format:H:i',
            'bo'                  => 'nullable|date_format:H:i',
            'ao'                  => 'nullable|date_format:H:i',
            'toleransi_terlambat' => 'required|integer|min:0|max:120',
        ]);

        $shift = Shift::create($validated);

        AuditLog::log('Menambah shift', $shift, null, $shift->toArray());

        return redirect()->route('master.shift.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        return view('master.shift.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:100',
            'jam_mulai'           => 'required|date_format:H:i',
            'bi'                  => 'nullable|date_format:H:i',
            'ai'                  => 'nullable|date_format:H:i',
            'jam_selesai'         => 'required|date_format:H:i',
            'bo'                  => 'nullable|date_format:H:i',
            'ao'                  => 'nullable|date_format:H:i',
            'toleransi_terlambat' => 'required|integer|min:0|max:120',
            'is_active'           => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $dataLama = $shift->toArray();
        $shift->update($validated);

        AuditLog::log('Update shift', $shift, $dataLama, $shift->fresh()->toArray());

        return redirect()->route('master.shift.index')
            ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $dataLama = $shift->toArray();
        $shift->delete();

        AuditLog::log('Menghapus shift', null, $dataLama, null);

        return redirect()->route('master.shift.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}
