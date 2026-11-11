<?php

namespace App\Services;

use Illuminate\Notifications\Notification;

class VerificationMail
{
    // Reject local-only transports, including a silent log fallback after SMTP fails.
    public function ensureDeliverable(?string $name = null, array $visited = []): void
    {
        $name ??= config('mail.default');
        if (in_array($name, $visited, true)) {
            throw new \RuntimeException('Verification mail transport is unavailable.');
        }
        $mailer = config("mail.mailers.{$name}", []);
        $transport = $mailer['transport'] ?? '';
        if ($transport === 'log' || ($transport === 'array' && ! app()->environment('testing')) || $transport === '') {
            throw new \RuntimeException('Verification mail requires an inbox delivery transport.');
        }
        foreach ($mailer['mailers'] ?? [] as $child) {
            $this->ensureDeliverable($child, [...$visited, $name]);
        }
    }

    public function send(object $recipient, Notification $notification): void
    {
        $this->ensureDeliverable();
        $recipient->notify($notification);
    }
}
