<?php

namespace App\Http\Controllers;

use App\Enums\UnitType;
use App\Models\AuditLog;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'pjlp']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = User::create([
            'name'      => $validated['name'],
            'nip'       => $validated['nip'] ?? null,
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'unit'      => $validated['unit'] ?? null,
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        AuditLog::log('Menambah user baru', $user, null, $user->toArray());

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        $dataLama = $user->toArray();

        $user->update([
            'name'      => $validated['name'],
            'nip'       => $validated['nip'] ?? null,
            'email'     => $validated['email'],
            'unit'      => $validated['unit'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        AuditLog::log('Update user', $user, $dataLama, $user->fresh()->toArray());

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $dataLama = $user->toArray();
        $user->delete();

        AuditLog::log('Menghapus user', null, $dataLama, null);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
