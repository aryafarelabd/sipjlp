@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark">
            <img src="{{ asset('images/logo-full.png') }}" alt="SiPJLP" height="70" class="mb-3">
        </a>
        <h2 class="h3 mb-1">Selamat Datang</h2>
        <p class="text-muted">Silakan masuk untuk melanjutkan</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-check icon alert-icon"></i></div>
                <div>{{ session('status') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-alert-circle icon alert-icon"></i></div>
                <div>{{ $errors->first('login') ?? 'Email/NIP atau password salah. Silakan coba lagi.' }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="card card-md shadow-sm">
        <div class="card-body">
            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label">
                        <i class="ti ti-user me-1 text-muted"></i> Username / NIP / Email
                    </label>
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                        placeholder="username, NIP, atau email" value="{{ old('login') }}" required autofocus>
                    <div class="form-hint">Gunakan username, NIP, atau email untuk login</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="ti ti-lock me-1 text-muted"></i> Password
                        <span class="form-label-description">
                            <a href="{{ route('password.request') }}" class="text-muted">Lupa password?</a>
                        </span>
                    </label>
                    <div class="input-group input-group-flat">
                        <input type="password" name="password" id="password-input" class="form-control"
                            placeholder="Masukkan password" required>
                        <span class="input-group-text">
                            <a href="#" class="link-secondary" title="Tampilkan password" data-bs-toggle="tooltip"
                                onclick="togglePassword(); return false;">
                                <i class="ti ti-eye" id="toggle-icon"></i>
                            </a>
                        </span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" />
                        <span class="form-check-label">Ingat saya di perangkat ini</span>
                    </label>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="ti ti-login me-2"></i> Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-muted mt-4">
        <small>&copy; {{ date('Y') }} SIPJLP - RSUD Cipayung Jakarta Timur</small>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password-input');
            var toggleIcon = document.getElementById('toggle-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'ti ti-eye-off';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'ti ti-eye';
            }
        }
    </script>
@endsection
