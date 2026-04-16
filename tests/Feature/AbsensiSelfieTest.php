<?php

namespace Tests\Feature;

use App\Enums\SumberDataAbsensi;
use App\Enums\StatusAbsensi;
use App\Enums\UnitType;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\JadwalShiftCs;
use App\Models\Pjlp;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsensiSelfieTest extends TestCase
{
    use RefreshDatabase;

    protected User $userPjlp;
    protected Pjlp $pjlp;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ShiftSeeder::class);
        Storage::fake('public');

        // Buat PJLP CS
        $this->userPjlp = User::factory()->create([
            'is_active' => true,
            'unit'      => UnitType::CLEANING,
        ]);
        $this->userPjlp->assignRole('pjlp');

        $this->pjlp = Pjlp::factory()->create([
            'user_id' => $this->userPjlp->id,
            'unit'    => UnitType::CLEANING,
        ]);

        $this->shift = Shift::where('nama', 'Pagi')->first();
    }

    private function buatJadwalCs(string $tanggal = null): JadwalShiftCs
    {
        return JadwalShiftCs::create([
            'pjlp_id'  => $this->pjlp->id,
            'tanggal'  => $tanggal ?? today()->toDateString(),
            'shift_id' => $this->shift->id,
            'status'   => 'normal',
        ]);
    }

    private function fakeFoto(): UploadedFile
    {
        return UploadedFile::fake()->image('selfie.jpg', 200, 200);
    }

    // ─── Halaman absen ────────────────────────────────────────────────────────

    public function test_halaman_absen_dapat_diakses_oleh_pjlp(): void
    {
        $this->actingAs($this->userPjlp)
            ->get(route('absen.index'))
            ->assertOk();
    }

    public function test_halaman_absen_tidak_dapat_diakses_tanpa_login(): void
    {
        $this->get(route('absen.index'))->assertRedirect(route('login'));
    }

    public function test_koordinator_tidak_bisa_akses_halaman_absen_pjlp(): void
    {
        $koordinator = User::factory()->create(['is_active' => true, 'unit' => UnitType::CLEANING]);
        $koordinator->assignRole('koordinator');

        // Koordinator tidak punya permission absensi selfie — expect 403
        $this->actingAs($koordinator)
            ->get(route('absen.index'))
            ->assertForbidden();
    }

    // ─── Absen Masuk ─────────────────────────────────────────────────────────

    public function test_absen_masuk_berhasil_dalam_window_waktu(): void
    {
        $this->buatJadwalCs();

        // Simulasi jam 07:00 (tepat jam shift)
        Carbon::setTestNow(today()->setTime(7, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => -6.2293,
                'longitude' => 106.8689,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('absensi', [
            'pjlp_id'     => $this->pjlp->id,
            'tanggal'     => today()->toDateString(),
            'sumber_data' => SumberDataAbsensi::SELFIE->value,
        ]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_tepat_waktu_status_hadir(): void
    {
        $this->buatJadwalCs();
        Carbon::setTestNow(today()->setTime(7, 0));

        $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $absensi = Absensi::where('pjlp_id', $this->pjlp->id)->first();
        $this->assertEquals(StatusAbsensi::HADIR, $absensi->status);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_terlambat_status_terlambat(): void
    {
        $this->buatJadwalCs();
        // Jam 07:30 — 30 menit setelah shift mulai, melebihi toleransi 15 menit
        Carbon::setTestNow(today()->setTime(7, 30));

        $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $absensi = Absensi::where('pjlp_id', $this->pjlp->id)->first();
        $this->assertEquals(StatusAbsensi::TERLAMBAT, $absensi->status);
        $this->assertGreaterThan(0, $absensi->menit_terlambat);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_dalam_toleransi_status_hadir(): void
    {
        $this->buatJadwalCs();
        // Jam 07:10 — 10 menit terlambat, masih dalam toleransi 15 menit
        Carbon::setTestNow(today()->setTime(7, 10));

        $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $absensi = Absensi::where('pjlp_id', $this->pjlp->id)->first();
        $this->assertEquals(StatusAbsensi::HADIR, $absensi->status);
        $this->assertEquals(0, $absensi->menit_terlambat);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_sebelum_window_ditolak(): void
    {
        $this->buatJadwalCs();
        // Jam 05:00 — sebelum window masuk (06:00)
        Carbon::setTestNow(today()->setTime(5, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('absensi', ['pjlp_id' => $this->pjlp->id]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_setelah_window_ditolak(): void
    {
        $this->buatJadwalCs();
        // Jam 09:00 — setelah window tutup (08:00)
        Carbon::setTestNow(today()->setTime(9, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('absensi', ['pjlp_id' => $this->pjlp->id]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_tidak_bisa_dua_kali(): void
    {
        $this->buatJadwalCs();
        Carbon::setTestNow(today()->setTime(7, 0));

        // Absen pertama
        $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        // Absen kedua — harus ditolak
        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, Absensi::where('pjlp_id', $this->pjlp->id)->count());

        Carbon::setTestNow();
    }

    // ─── Absen Pulang ─────────────────────────────────────────────────────────

    public function test_absen_pulang_berhasil_setelah_masuk(): void
    {
        $this->buatJadwalCs();

        // Sudah absen masuk
        Absensi::create([
            'pjlp_id'         => $this->pjlp->id,
            'tanggal'         => today()->toDateString(),
            'shift_id'        => $this->shift->id,
            'jam_masuk'       => '07:00:00',
            'status'          => StatusAbsensi::HADIR,
            'menit_terlambat' => 0,
            'sumber_data'     => SumberDataAbsensi::SELFIE,
        ]);

        // Simulasi jam 15:00 (jam pulang shift Pagi)
        Carbon::setTestNow(today()->setTime(15, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.pulang'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => -6.2293,
                'longitude' => 106.8689,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $absensi = Absensi::where('pjlp_id', $this->pjlp->id)->first();
        $this->assertNotNull($absensi->jam_pulang);

        Carbon::setTestNow();
    }

    public function test_absen_pulang_tanpa_absen_masuk_ditolak(): void
    {
        $this->buatJadwalCs();
        Carbon::setTestNow(today()->setTime(15, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.pulang'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertRedirect();

        Carbon::setTestNow();
    }

    public function test_absen_pulang_tidak_bisa_dua_kali(): void
    {
        $this->buatJadwalCs();

        Absensi::create([
            'pjlp_id'         => $this->pjlp->id,
            'tanggal'         => today()->toDateString(),
            'shift_id'        => $this->shift->id,
            'jam_masuk'       => '07:00:00',
            'jam_pulang'      => '15:00:00',
            'status'          => StatusAbsensi::HADIR,
            'menit_terlambat' => 0,
            'sumber_data'     => SumberDataAbsensi::SELFIE,
        ]);

        Carbon::setTestNow(today()->setTime(15, 30));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.pulang'), [
                'foto'      => $this->fakeFoto(),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertRedirect();
        // jam_pulang tidak berubah dari 15:00
        $absensi = Absensi::where('pjlp_id', $this->pjlp->id)->first();
        $this->assertEquals('15:00:00', $absensi->jam_pulang->format('H:i:s'));

        Carbon::setTestNow();
    }

    // ─── Foto validation ──────────────────────────────────────────────────────

    public function test_absen_masuk_tanpa_foto_ditolak(): void
    {
        $this->buatJadwalCs();
        Carbon::setTestNow(today()->setTime(7, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertSessionHasErrors('foto');

        Carbon::setTestNow();
    }

    public function test_absen_masuk_foto_bukan_gambar_ditolak(): void
    {
        $this->buatJadwalCs();
        Carbon::setTestNow(today()->setTime(7, 0));

        $response = $this->actingAs($this->userPjlp)
            ->post(route('absen.masuk'), [
                'foto'      => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                'latitude'  => null,
                'longitude' => null,
            ]);

        $response->assertSessionHasErrors('foto');

        Carbon::setTestNow();
    }
}
