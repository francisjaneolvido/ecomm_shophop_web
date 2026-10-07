<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiderVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $logisticsName,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ShopHop Rider Email Verification Code')
            ->greeting('Welcome to ShopHop!')
            ->line('Thank you for applying as a Rider / Courier.')
            ->line('Your rider verification code is:')
            ->line($this->code)
            ->line('This code will expire in 10 minutes.')
            ->line('Do not share this code with anyone.')
            ->line("After email verification, your application will be reviewed by {$this->logisticsName}.");
    }
}
