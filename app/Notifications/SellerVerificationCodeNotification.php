<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerVerificationCodeNotification extends Notification
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
            ->subject('ShopHop Seller Email Verification Code')
            ->greeting('Welcome to ShopHop!')
            ->line('Thank you for registering as a seller.')
            ->line('Your seller verification code is:')
            ->line($this->code)
            ->line('This code will expire in 10 minutes.')
            ->line('Do not share this code with anyone.')
            ->line('After verifying your email, your seller registration will be submitted for administrator approval.');
    }
}