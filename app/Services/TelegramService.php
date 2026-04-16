<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token  = config('services.telegram.token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Kirim pesan teks ke chat_id tertentu.
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        try {
            $response = Http::timeout(5)->post("{$this->apiUrl}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram sendMessage gagal', [
                    'chat_id'  => $chatId,
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil update terbaru dari bot (polling).
     * Digunakan untuk mengambil chat_id saat user ketik /start.
     */
    public function getUpdates(int $offset = 0): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiUrl}/getUpdates", [
                'offset'  => $offset,
                'limit'   => 100,
                'timeout' => 5,
            ]);

            return $response->json('result') ?? [];
        } catch (\Exception $e) {
            Log::error('Telegram getUpdates exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Test koneksi bot.
     */
    public function getMe(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/getMe");
            return $response->json('result');
        } catch (\Exception $e) {
            return null;
        }
    }
}
