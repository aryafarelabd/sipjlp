@extends('layouts.app')

@section('title', 'Absensi Harian')

@push('styles')
<style>
    .absen-card {
        max-width: 480px;
        margin: 0 auto;
    }
    #video-preview, #canvas-preview {
        width: 100%;
        border-radius: 8px;
        background: #000;
        max-height: 320px;
        object-fit: cover;
    }
    #canvas-preview { display: none; }
    .gps-status { font-size: 0.85rem; }
    .window-info { font-size: 0.875rem; }
    .status-badge-masuk, .status-badge-pulang {
        font-size: 1rem;
        padding: 0.4rem 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Absensi Harian</h2>
                <div class="text-muted">{{ $now->translatedFormat('l, d F Y') }}</div>
            </div>
        </div>
    </div>

    <div class="absen-card">

        {{-- Alert flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible mb-3">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible mb-3">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('error') }}
            </div>
        @endif

        {{-- Info PJLP --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md">
                        @if($pjlp->foto)
                            <img src="{{ asset('storage/pjlp/' . $pjlp->foto) }}" alt="{{ $pjlp->nama }}">
                        @else
                            <span class="avatar-initials bg-blue text-white">{{ strtoupper(substr($pjlp->nama, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold">{{ $pjlp->nama }}</div>
                        <div class="text-muted small">{{ $pjlp->jabatan }} &bull; {{ ucfirst($pjlp->unit->value) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATE A: Tidak ada jadwal --}}
        @if(!$hasJadwal)
            <div class="card border-warning">
                <div class="card-body text-center py-4">
                    <div class="mb-2"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-warning" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg></div>
                    <h4 class="text-warning mb-1">Tidak Ada Jadwal Hari Ini</h4>
                    <p class="text-muted mb-0">Hubungi koordinator untuk informasi jadwal Anda.</p>
                </div>
            </div>

        @elseif($shift)
            {{-- Info Shift --}}
            <div class="card mb-3 bg-blue-lt">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $shift->nama }}</div>
                            <div class="text-muted small">
                                Masuk: {{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }} &bull;
                                Pulang: {{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}
                            </div>
                        </div>
                        <span class="badge bg-blue">Jadwal Hari Ini</span>
                    </div>
                </div>
            </div>

            {{-- Status absensi hari ini --}}
            @if($absensiHariIni)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="text-muted small">Masuk</div>
                                @if($absensiHariIni->jam_masuk)
                                    <div class="fw-bold text-success fs-4">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }}</div>
                                    <span class="badge {{ $absensiHariIni->status->value === 'terlambat' ? 'bg-warning' : 'bg-success' }}">
                                        {{ $absensiHariIni->status->value === 'terlambat' ? 'Terlambat' : 'Tepat Waktu' }}
                                    </span>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Pulang</div>
                                @if($absensiHariIni->jam_pulang)
                                    <div class="fw-bold text-success fs-4">{{ \Carbon\Carbon::parse($absensiHariIni->jam_pulang)->format('H:i') }}</div>
                                    <span class="badge bg-success">Tercatat</span>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- STATE F: Sudah absen masuk dan pulang --}}
            @if($absensiHariIni && $absensiHariIni->jam_masuk && $absensiHariIni->jam_pulang)
                <div class="card border-success">
                    <div class="card-body text-center py-4">
                        <div class="mb-2 text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg></div>
                        <h4 class="text-success mb-1">Absensi Hari Ini Selesai</h4>
                        <p class="text-muted mb-0">Masuk pukul {{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }} &bull; Pulang pukul {{ \Carbon\Carbon::parse($absensiHariIni->jam_pulang)->format('H:i') }}</p>
                    </div>
                </div>

            @else
                {{-- FORM ABSEN MASUK (State B / C) --}}
                @if(!$absensiHariIni || !$absensiHariIni->jam_masuk)
                    @if(!$masukStatus['allowed'])
                        <div class="card mb-3 {{ str_contains($masukStatus['reason'], 'ditutup') || str_contains($masukStatus['reason'], 'ALPHA') ? 'border-danger' : 'border-azure' }}">
                            <div class="card-body text-center py-3">
                                <p class="mb-0 {{ str_contains($masukStatus['reason'], 'ditutup') || str_contains($masukStatus['reason'], 'ALPHA') ? 'text-danger' : 'text-azure' }}">
                                    {{ $masukStatus['reason'] }}
                                </p>
                                @if(isset($masukStatus['window']))
                                    <div class="text-muted small mt-1 window-info">
                                        Window absen masuk: {{ $masukStatus['window']['open']->format('H:i') }} – {{ $masukStatus['window']['close']->format('H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-green" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                                    Absen Masuk
                                </h4>
                                <div class="text-muted small window-info">
                                    Window: {{ $masukStatus['window']['open']->format('H:i') }} – {{ $masukStatus['window']['close']->format('H:i') }}
                                </div>
                            </div>
                            <div class="card-body">
                                @include('absensi.selfie._form-absen', [
                                    'action' => route('absen.masuk'),
                                    'btnLabel' => 'Absen Masuk',
                                    'btnClass' => 'btn-success',
                                    'formId' => 'form-masuk',
                                ])
                            </div>
                        </div>
                    @endif
                @endif

                {{-- FORM ABSEN PULANG (State D / E) --}}
                @if($absensiHariIni && $absensiHariIni->jam_masuk && !$absensiHariIni->jam_pulang)
                    @if(!$pulangStatus['allowed'])
                        <div class="card mb-3 border-azure">
                            <div class="card-body text-center py-3">
                                <p class="mb-0 text-azure">{{ $pulangStatus['reason'] }}</p>
                                @if(isset($pulangStatus['window']))
                                    <div class="text-muted small mt-1 window-info">
                                        Window absen pulang: {{ $pulangStatus['window']['open']->format('H:i') }} – {{ $pulangStatus['window']['close']->format('H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-blue" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                                    Absen Pulang
                                </h4>
                                <div class="text-muted small window-info">
                                    Window: {{ $pulangStatus['window']['open']->format('H:i') }} – {{ $pulangStatus['window']['close']->format('H:i') }}
                                </div>
                            </div>
                            <div class="card-body">
                                @include('absensi.selfie._form-absen', [
                                    'action' => route('absen.pulang'),
                                    'btnLabel' => 'Absen Pulang',
                                    'btnClass' => 'btn-blue',
                                    'formId' => 'form-pulang',
                                ])
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-request GPS
    const latInput  = document.querySelectorAll('input[name="latitude"]');
    const lonInput  = document.querySelectorAll('input[name="longitude"]');
    const gpsStatus = document.querySelectorAll('.gps-status-text');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                latInput.forEach(el => el.value = pos.coords.latitude);
                lonInput.forEach(el => el.value = pos.coords.longitude);
                gpsStatus.forEach(el => {
                    el.innerHTML = '<span class="text-success">✓ Lokasi ditemukan</span>';
                });
            },
            () => {
                gpsStatus.forEach(el => {
                    el.innerHTML = '<span class="text-warning">⚠ Lokasi tidak tersedia</span>';
                });
            },
            { timeout: 10000, maximumAge: 60000 }
        );
    }

    // Camera & capture logic per form
    document.querySelectorAll('.absen-form-wrapper').forEach(function (wrapper) {
        const video     = wrapper.querySelector('video');
        const canvas    = wrapper.querySelector('canvas');
        const btnKamera = wrapper.querySelector('.btn-kamera');
        const btnAmbil  = wrapper.querySelector('.btn-ambil');
        const btnUlangi = wrapper.querySelector('.btn-ulangi');
        const btnSubmit = wrapper.querySelector('.btn-submit');
        const fotoInput = wrapper.querySelector('input.foto-input');
        let stream      = null;

        btnKamera.addEventListener('click', async function () {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                video.srcObject = stream;
                video.style.display = 'block';
                btnKamera.style.display = 'none';
                btnAmbil.style.display  = 'inline-block';
            } catch (e) {
                alert('Kamera tidak dapat diakses. Pastikan izin kamera diberikan di browser.');
            }
        });

        btnAmbil.addEventListener('click', function () {
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.style.display = 'block';
            video.style.display  = 'none';
            btnAmbil.style.display  = 'none';
            btnUlangi.style.display = 'inline-block';
            btnSubmit.disabled = false;

            // Stop stream
            if (stream) stream.getTracks().forEach(t => t.stop());

            // Convert canvas to file input
            canvas.toBlob(function (blob) {
                const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
                const dt   = new DataTransfer();
                dt.items.add(file);
                fotoInput.files = dt.files;
            }, 'image/jpeg', 0.85);
        });

        btnUlangi.addEventListener('click', async function () {
            canvas.style.display    = 'none';
            btnUlangi.style.display = 'none';
            btnSubmit.disabled      = true;

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                video.srcObject = stream;
                video.style.display = 'block';
                btnAmbil.style.display = 'inline-block';
            } catch (e) {
                btnKamera.style.display = 'inline-block';
            }
        });

        // Loading state on submit
        wrapper.closest('form').addEventListener('submit', function () {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        });
    });
});
</script>
@endpush
