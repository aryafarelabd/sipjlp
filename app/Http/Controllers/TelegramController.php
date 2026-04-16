<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    /**
     * Halaman hubungkan Telegram milik user yang sedang login.
     */
    public function index()
    {
        $user      = auth()->user();
        $botUsername = config('services.telegram.username');

        return view('telegram.index', compact('user', 'botUsername'));
    }

    /**
     * Polling: ambil update dari bot, cocokkan kode unik, simpan chat_id.
     * Dipanggil via AJAX dari halaman hubungkan Telegram.
     */
    public function pollConnect(Request $request)
    {
        $user = auth()->user();
        $kode = $request->input('kode');

        if (!$kode || strlen($kode) < 6) {
            return response()->json(['connected' => false]);
        }

        $updates = $this->telegram->getUpdates();

        foreach ($updates as $update) {
            $text   = $update['message']['text'] ?? '';
            $chatId = (string) ($update['message']['chat']['id'] ?? '');
            $from   = $update['message']['from']['id'] ?? null;

            // Cek apakah pesan mengandung kode verifikasi unik user
            if (str_contains($text, $kode) && $chatId) {
                // Pastikan chat_id belum dipakai user lain
                $existing = User::where('telegram_chat_id', $chatId)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($existing) {
                    return response()->json([
                        'connected' => false,
                        'message'   => 'Chat ID ini sudah terhubung ke akun lain.',
                    ]);
                }

                $user->update(['telegram_chat_id' => $chatId]);

                // Kirim konfirmasi ke user
                $this->telegram->sendMessage(
                    $chatId,
                    "✅ <b>Berhasil terhubung!</b>\n\n" .
                    "Akun SIPJLP <b>{$user->name}</b> kini terhubung ke Telegram.\n" .
                    "Kamu akan menerima notifikasi penting di sini."
                );

                AuditLog::log('Hubungkan Telegram', $user);

                return response()->json(['connected' => true]);
            }
        }

        return response()->json(['connected' => false]);
    }

    /**
     * Putuskan koneksi Telegram.
     */
    public function disconnect()
    {
        $user = auth()->user();
        $user->update(['telegram_chat_id' => null]);

        AuditLog::log('Putuskan Telegram', $user);

        return back()->with('success', 'Koneksi Telegram berhasil diputus.');
    }

    /**
     * Test kirim pesan ke user yang sedang login.
     */
    public function testKirim()
    {
        $user = auth()->user();

        if (!$user->telegram_chat_id) {
            return back()->with('error', 'Telegram belum terhubung.');
        }

        $sent = $this->telegram->sendMessage(
            $user->telegram_chat_id,
            "🔔 <b>Ini adalah pesan test dari SIPJLP</b>\n\nNotifikasi kamu berfungsi dengan baik!"
        );

        return back()->with(
            $sent ? 'success' : 'error',
            $sent ? 'Pesan test berhasil dikirim ke Telegram kamu.' : 'Gagal mengirim pesan. Periksa koneksi.'
        );
    }
}
