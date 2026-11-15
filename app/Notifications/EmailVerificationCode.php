<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCode extends Notification
{
    public function __construct(public string $code) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode verifikasi email DevFlow')
            ->greeting('Verifikasi alamat email Anda')
            ->line('Masukkan kode berikut untuk menyelesaikan pendaftaran DevFlow.')
            ->line($this->code)
            ->line('Kode ini berlaku selama 10 menit dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak membuat akun DevFlow, abaikan email ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
