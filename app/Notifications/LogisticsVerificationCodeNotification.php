<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LogisticsVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ShopHop Logistics Email Verification Code')
            ->greeting('Welcome to ShopHop!')
            ->line('Thank you for registering a Logistics / Sorting Center account.')
            ->line('Your verification code is:')
            ->line($this->code)
            ->line('This code will expire in 10 minutes.')
            ->line('Do not share this code with anyone.')
            ->line('After verification and submission, your application will wait for administrator approval.');
    }
}
