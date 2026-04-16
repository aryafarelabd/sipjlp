<form action="{{ route('logbook-limbah.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Tanggal Pekerjaan</label>
        <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Detail Pekerjaan Limbah</label>
        <input type="text" name="pekerjaan" class="form-control" placeholder="Contoh: Pengangkatan Limbah Medis" required>
    </div>
    <div class="mb-3">
        <label>Shift</label>
        <select name="shift_id" class="form-select">
            {{-- Loop data shift dari database --}}
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->nama }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Ke Lembar Kerja</button>
</form>