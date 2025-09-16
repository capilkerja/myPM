<?php

namespace App\Notifications;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewTicketAssigned extends Notification
{
    use Queueable;

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
    // public function via(object $notifiable): array
    // {
    //     // return ['mail'];
    //     return [TelegramChannel::class];
    // }
    public function via($notifiable)
    {
        return ['telegram'];
    }
    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    public function toTelegram(object $notifiable)
    {
        $ticket = $this->ticket;
        $ticketUrl = TicketResource::getUrl('view', ['record' => $ticket->id]);

        $startDate = $ticket->start_date ? \Carbon\Carbon::parse($ticket->start_date)->format('d M Y') : '-';
        $endDate = $ticket->due_date ? \Carbon\Carbon::parse($ticket->due_date)->format('d M Y') : '-';

        // $notifiable adalah objek User yang menerima notifikasi.
        // Jadi kita gunakan namanya untuk sapaan yang lebih personal.
        $assigneeName = $notifiable->name;

        $description = strip_tags($ticket->description);

        // Hapus ->to(...) karena Laravel sudah menanganinya secara otomatis
        return TelegramMessage::create()
            ->content(
                "👋 *Halo {$assigneeName}*, Anda mendapatkan ticket baru.\n\n" .
                    "🎫 *Ticket Baru Ditugaskan:*\n" .
                    "📌 *Nama Ticket:* {$ticket->name}\n" .
                    "🆔 *Kode Ticket:* {$ticket->uuid}\n" .
                    "📝 *Deskripsi:* {$description}\n" .
                    "📅 *Tanggal Mulai:* {$startDate}\n" .
                    "📅 *Tanggal Selesai:* {$endDate}\n"
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
