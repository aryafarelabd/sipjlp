<?php

namespace App\Notifications;

use App\Models\Cuti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CutiDiajukanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Cuti $cuti) {}

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): string
    {
        $pjlp     = $this->cuti->pjlp;
        $jenis    = $this->cuti->jenisCuti?->nama ?? '-';
        $mulai    = $this->cuti->tgl_mulai->format('d M Y');
        $selesai  = $this->cuti->tgl_selesai->format('d M Y');
        $durasi   = $this->cuti->tgl_mulai->diffInDays($this->cuti->tgl_selesai) + 1;
        $alasan   = $this->cuti->alasan ?? '-';

        return
            "🗓️ <b>Pengajuan Cuti Baru</b>\n\n" .
            "👤 <b>{$pjlp->nama}</b>\n" .
            "📋 Jenis: {$jenis}\n" .
            "📅 {$mulai} – {$selesai} ({$durasi} hari)\n" .
            "💬 Alasan: {$alasan}\n\n" .
            "Silakan buka SIPJLP untuk menyetujui atau menolak.";
    }
}
