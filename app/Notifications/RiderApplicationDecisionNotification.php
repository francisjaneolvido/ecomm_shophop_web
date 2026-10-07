<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiderApplicationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly bool $approved,
        private readonly string $logisticsName,
        private readonly ?string $reason = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->approved
                ? 'ShopHop Rider Application Approved'
                : 'ShopHop Rider Application Update')
            ->greeting('Hello '.$notifiable->name.',');

        if ($this->approved) {
            return $mail
                ->line("Your Rider application with {$this->logisticsName} has been approved.")
                ->line('You can now sign in to the ShopHop mobile app and receive pickup or delivery assignments.')
                ->line('Your delivery coverage area may be assigned or updated by the Logistics / Sorting Center.');
        }

        $mail->line("Your Rider application with {$this->logisticsName} was not approved.");
        if ($this->reason) {
            $mail->line('Reason: '.$this->reason);
        }

        return $mail->line('Please contact the Logistics / Sorting Center if you need clarification.');
    }
}
