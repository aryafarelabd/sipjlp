<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="{{ $formId }}">
    @csrf
    <div class="absen-form-wrapper">

        {{-- GPS hidden inputs --}}
        <input type="hidden" name="latitude">
        <input type="hidden" name="longitude">

        {{-- GPS status --}}
        <div class="mb-2 gps-status text-muted gps-status-text">
            <span class="spinner-border spinner-border-sm me-1"></span> Mendapatkan lokasi...
        </div>

        {{-- Camera / Preview area --}}
        <div class="mb-3">
            <video id="video-preview" autoplay playsinline style="display:none;"></video>
            <canvas id="canvas-preview"></canvas>
        </div>

        {{-- Foto file input (hidden, filled by JS) --}}
        <input type="file" name="foto" class="foto-input d-none" accept="image/*">

        {{-- Validation error --}}
        @error('foto')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror

        {{-- Camera buttons --}}
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-secondary btn-kamera w-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 0 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"/><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg>
                Aktifkan Kamera
            </button>

            <button type="button" class="btn btn-primary btn-ambil w-100" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="3"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 0 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"/></svg>
                Ambil Foto
            </button>

            <button type="button" class="btn btn-warning btn-ulangi" style="display:none;">
                Ulangi
            </button>

            <button type="submit" class="btn {{ $btnClass }} btn-submit w-100" disabled>
                {{ $btnLabel }}
            </button>
        </div>

    </div>
</form>
