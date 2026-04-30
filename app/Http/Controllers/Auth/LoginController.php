<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ], [
            'login.required'    => 'Email atau NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = $request->input('login');

        // Deteksi otomatis: '@' → email, angka semua → NIP, lainnya → username
        if (str_contains($loginInput, '@')) {
            $field = 'email';
        } elseif (ctype_digit($loginInput)) {
            $field = 'nip';
        } else {
            $field = 'username';
        }

        $credentials = [
            $field     => $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            AuditLog::log('Login berhasil');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Email/NIP atau password salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        AuditLog::log('Logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
