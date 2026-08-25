<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu código de verificação - Renick Kids')
            ->greeting('Olá!')
            ->line('Utilize o código de 6 dígitos abaixo para concluir sua autenticação:')
            ->line("**{$this->code}**")
            ->line('Este código expira em 10 minutos.')
            ->salutation('Atenciosamente, Renick Kids.');
    }
}