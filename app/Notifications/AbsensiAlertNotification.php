<?php

namespace App\Notifications;

use App\Models\Absensi;
use App\Models\Pjlp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AbsensiAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Pjlp $pjlp,
        private string $tipe, // 'terlambat' | 'alpha'
        private ?Absensi $absensi = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): string
    {
        $tanggal = $this->absensi?->tanggal
            ? \Carbon\Carbon::parse($this->absensi->tanggal)->format('d M Y')
            : today()->format('d M Y');

        if ($this->tipe === 'terlambat') {
            $menit = $this->absensi?->menit_terlambat ?? 0;
            $jam   = $this->absensi?->jam_masuk ?? '-';

            return
                "⚠️ <b>PJLP Terlambat</b>\n\n" .
                "👤 <b>{$this->pjlp->nama}</b>\n" .
                "📅 {$tanggal}\n" .
                "🕐 Absen masuk: {$jam}\n" .
                "⏱️ Terlambat: {$menit} menit";
        }

        return
            "🔴 <b>PJLP Tidak Hadir (Alpha)</b>\n\n" .
            "👤 <b>{$this->pjlp->nama}</b>\n" .
            "📅 {$tanggal}\n\n" .
            "PJLP ini tidak melakukan absen masuk hingga window waktu ditutup.";
    }
}
