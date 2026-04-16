<?php

namespace App\Http\Controllers;

use App\Enums\StatusPjlp;
use App\Enums\UnitType;
use App\Models\AuditLog;
use App\Models\Pjlp;
use App\Models\User;
use App\Http\Requests\StorePjlpRequest;
use App\Http\Requests\UpdatePjlpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PjlpController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Pjlp::with('user');

        // Filter berdasarkan role
        if ($user->hasRole('koordinator')) {
            $query->forKoordinator($user);
        }

        // Filter unit
        if ($request->filled('unit')) {
            $query->unit($request->unit);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $pjlp = $query->latest()->paginate(15);

        return view('pjlp.index', compact('pjlp'));
    }

    public function create()
    {
        $this->authorize('create', Pjlp::class);
        return view('pjlp.create');
    }

    public function store(StorePjlpRequest $request)
    {
        $this->authorize('create', Pjlp::class);

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $userId = null;

            // Create user account if requested
            if ($request->boolean('create_user')) {
                $user = User::create([
                    'name' => $validated['nama'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'is_active' => true,
                ]);
                $user->assignRole('pjlp');
                $userId = $user->id;
            }

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('pjlp', 'public');
                $fotoPath = basename($fotoPath);
            }

            $pjlp = Pjlp::create([
                'user_id' => $userId,
                'nip' => $validated['nip'],
                'nama' => $validated['nama'],
                'unit' => $validated['unit'],
                'jabatan' => $validated['jabatan'],
                'no_telp' => $validated['no_telp'],
                'alamat' => $validated['alamat'],
                'tanggal_bergabung' => $validated['tanggal_bergabung'],
                'foto' => $fotoPath,
                'status' => StatusPjlp::AKTIF,
            ]);

            AuditLog::log('Menambah data PJLP', $pjlp, null, $pjlp->toArray());

            DB::commit();

            return redirect()->route('pjlp.index')
                ->with('success', 'Data PJLP berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Pjlp $pjlp)
    {
        $this->authorize('view', $pjlp);
        $pjlp->load(['user', 'absensi' => fn($q) => $q->latest()->take(10)]);
        return view('pjlp.show', compact('pjlp'));
    }

    public function edit(Pjlp $pjlp)
    {
        $this->authorize('update', $pjlp);
        return view('pjlp.edit', compact('pjlp'));
    }

    public function update(UpdatePjlpRequest $request, Pjlp $pjlp)
    {
        $this->authorize('update', $pjlp);

        $validated = $request->validated();

        $dataLama = $pjlp->toArray();

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto jika ada dan file-nya masih exist
            if ($pjlp->foto && Storage::disk('public')->exists('pjlp/' . $pjlp->foto)) {
                Storage::disk('public')->delete('pjlp/' . $pjlp->foto);
            }
            $fotoPath = $request->file('foto')->store('pjlp', 'public');
            $validated['foto'] = basename($fotoPath);
        }

        $pjlp->update($validated);

        // Update linked user name if exists
        if ($pjlp->user) {
            $pjlp->user->update(['name' => $validated['nama']]);
        }

        AuditLog::log('Mengupdate data PJLP', $pjlp, $dataLama, $pjlp->fresh()->toArray());

        return redirect()->route('pjlp.index')
            ->with('success', 'Data PJLP berhasil diperbarui.');
    }

    public function destroy(Pjlp $pjlp)
    {
        $this->authorize('delete', $pjlp);

        $dataLama = $pjlp->toArray();

        // Soft delete
        $pjlp->delete();

        AuditLog::log('Menghapus data PJLP', $pjlp, $dataLama, null);

        return redirect()->route('pjlp.index')
            ->with('success', 'Data PJLP berhasil dihapus.');
    }
}
