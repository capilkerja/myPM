<?php

namespace App\Notifications;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use NotificationChannels\Telegram\TelegramMessage;

class TicketCompletedNotification extends Notification
{
    use Queueable;

    protected Ticket $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $ticket = $this->ticket;
        $ticketUrl = TicketResource::getUrl('view', ['record' => $ticket->id]);

        // $notifiable di sini adalah si pembuat tiket (creator)
        $creatorName = $notifiable->name;

        return TelegramMessage::create()
            ->content(
                "✅ *Ticket Selesai Dikerjakan!*\n\n" .
                    "Halo *{$creatorName}*, ticket yang Anda buat telah diselesaikan.\n\n" .
                    "📌 *Nama Ticket:* {$ticket->name}\n" .
                    "🆔 *Kode Ticket:* `{$ticket->uuid}`\n\n" .
                    "Silakan periksa detailnya."
            )
            ->button('Lihat Detail Ticket', $ticketUrl);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
