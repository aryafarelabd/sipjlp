<?php

namespace App\Notifications;

use App\Models\Cuti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CutiDiputuskanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Cuti $cuti) {}

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): string
    {
        $disetujui = $this->cuti->status->value === 'approved';
        $icon      = $disetujui ? '✅' : '❌';
        $status    = $disetujui ? 'DISETUJUI' : 'DITOLAK';
        $jenis     = $this->cuti->jenisCuti?->nama ?? '-';
        $mulai     = $this->cuti->tgl_mulai->format('d M Y');
        $selesai   = $this->cuti->tgl_selesai->format('d M Y');
        $catatan   = $this->cuti->catatan_koordinator ?? '-';

        return
            "{$icon} <b>Pengajuan Cuti {$status}</b>\n\n" .
            "📋 Jenis: {$jenis}\n" .
            "📅 {$mulai} – {$selesai}\n" .
            "💬 Catatan: {$catatan}";
    }
}
