<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LogisticsApplicationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly bool $approved,
        private readonly ?string $rejectionReason = null,
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
                ? 'ShopHop Logistics / Sorting Center Application Approved'
                : 'ShopHop Logistics / Sorting Center Application Update')
            ->greeting('ShopHop Logistics / Sorting Center Application');

        if ($this->approved) {
            return $mail
                ->line('Your Logistics / Sorting Center application has been approved by the ShopHop administrator.')
                ->line('You may now sign in using the email address and password you registered.')
                ->line('Your approved center can now manage riders, parcel sorting, assignments, and delivery monitoring.');
        }

        return $mail
            ->line('Your Logistics / Sorting Center application was not approved at this time.')
            ->line('Reason: '.($this->rejectionReason ?: 'No reason was provided.'))
            ->line('Please contact ShopHop support or submit corrected information if re-application is allowed.');
    }
}
