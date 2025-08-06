<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(public string $name, public ?string $email = null, public ?string $phone = null) {}

    public function via(): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toDatabase(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => 'A new user has registered.'
        ];
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => 'A new user has registered.'
        ]);
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Laravel Starter!')
            ->greeting('Hello ' . $this->name . '!')
            ->line('Thank you for registering with Laravel Starter.')
            ->line('Your phone: ' . $this->phone)
            ->line('We are excited to have you on board!')
            ->action('Visit our site', url('/'))
            ->line('If you have any questions, feel free to reply to this email.');
    }
}
