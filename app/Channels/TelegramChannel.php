<?php

namespace App\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    public function __construct(private TelegramService $telegram) {}

    public function send(object $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->telegram_chat_id;

        if (!$chatId) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        $this->telegram->sendMessage($chatId, $message);
    }
}
