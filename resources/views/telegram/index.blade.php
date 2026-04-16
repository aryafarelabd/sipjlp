@extends('layouts.app')

@section('title', 'Hubungkan Telegram')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible mb-3">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-brand-telegram me-2 text-primary"></i>Notifikasi Telegram
                </h3>
            </div>
            <div class="card-body">

                @if($user->telegram_chat_id)
                {{-- Sudah terhubung --}}
                <div class="text-center py-3">
                    <div class="mb-3">
                        <span class="avatar avatar-lg bg-green-lt">
                            <i class="ti ti-brand-telegram fs-2 text-green"></i>
                        </span>
                    </div>
                    <h3 class="text-green mb-1">Telegram Terhubung</h3>
                    <p class="text-muted mb-4">Kamu akan menerima notifikasi penting langsung di Telegram.</p>

                    <div class="d-flex gap-2 justify-content-center">
                        <form action="{{ route('telegram.test') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-send me-1"></i>Kirim Pesan Test
                            </button>
                        </form>
                        <form action="{{ route('telegram.disconnect') }}" method="POST"
                              onsubmit="return confirm('Putuskan koneksi Telegram?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="ti ti-unlink me-1"></i>Putuskan
                            </button>
                        </form>
                    </div>
                </div>

                @else
                {{-- Belum terhubung --}}
                @php
                    // Kode unik 8 karakter: user_id + timestamp hash
                    $kode = 'SIPJLP' . strtoupper(substr(md5($user->id . $user->created_at), 0, 6));
                @endphp

                <div class="mb-4">
                    <div class="steps steps-counter">
                        <div class="step-item active">
                            <div class="h4 mb-1">Buka Telegram</div>
                            <p class="text-muted small">Cari bot <strong>@{{ $botUsername }}</strong> di Telegram</p>
                        </div>
                        <div class="step-item">
                            <div class="h4 mb-1">Kirim Kode</div>
                            <p class="text-muted small">Ketik dan kirim kode berikut ke bot:</p>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control font-monospace fw-bold text-center fs-4"
                                       id="kodeInput" value="{{ $kode }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyKode()">
                                    <i class="ti ti-copy"></i>
                                </button>
                            </div>
                            <div class="text-muted small">atau klik tombol di bawah untuk buka bot langsung:</div>
                            <a href="https://t.me/{{ $botUsername }}?start={{ $kode }}"
                               target="_blank" class="btn btn-primary mt-2 w-100">
                                <i class="ti ti-brand-telegram me-2"></i>Buka @{{ $botUsername }}
                            </a>
                        </div>
                        <div class="step-item">
                            <div class="h4 mb-1">Tunggu Verifikasi</div>
                            <p class="text-muted small">Sistem akan otomatis mendeteksi setelah kamu mengirim kode.</p>
                        </div>
                    </div>
                </div>

                <div id="statusBox" class="alert alert-info d-none">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" id="spinner"></div>
                        <span id="statusText">Menunggu kamu mengirim kode ke bot...</span>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="button" class="btn btn-success" id="btnVerifikasi" onclick="mulaiPolling()">
                        <i class="ti ti-check me-2"></i>Saya sudah kirim kode — Verifikasi Sekarang
                    </button>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const kode    = '{{ $kode ?? "" }}';
const pollUrl = '{{ route("telegram.poll") }}';
let polling   = false;
let attempts  = 0;

function copyKode() {
    navigator.clipboard.writeText(document.getElementById('kodeInput').value);
    const btn = document.querySelector('[onclick="copyKode()"]');
    btn.innerHTML = '<i class="ti ti-check"></i>';
    setTimeout(() => btn.innerHTML = '<i class="ti ti-copy"></i>', 2000);
}

function mulaiPolling() {
    if (polling) return;
    polling = true;
    attempts = 0;

    document.getElementById('btnVerifikasi').disabled = true;
    document.getElementById('statusBox').classList.remove('d-none');

    poll();
}

function poll() {
    if (attempts >= 20) {
        document.getElementById('statusText').textContent = 'Timeout. Pastikan kamu sudah mengirim kode yang benar ke bot.';
        document.getElementById('spinner').classList.add('d-none');
        document.getElementById('btnVerifikasi').disabled = false;
        polling = false;
        return;
    }

    attempts++;
    document.getElementById('statusText').textContent = `Menunggu... (${attempts}/20)`;

    fetch(pollUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ kode }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.connected) {
            document.getElementById('statusBox').className = 'alert alert-success';
            document.getElementById('spinner').classList.add('d-none');
            document.getElementById('statusText').textContent = '✅ Berhasil terhubung! Halaman akan direfresh...';
            setTimeout(() => location.reload(), 2000);
        } else {
            setTimeout(poll, 3000);
        }
    })
    .catch(() => setTimeout(poll, 3000));
}
</script>
@endpush
