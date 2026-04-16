<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('aktivitas')) {
            $query->where('aktivitas', 'like', "%{$request->aktivitas}%");
        }

        if ($request->filled('dari')) {
            $query->where('waktu', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->where('waktu', '<=', $request->sampai . ' 23:59:59');
        }

        $logs  = $query->latest('waktu')->paginate(50);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('audit-log.index', compact('logs', 'users'));
    }
}
