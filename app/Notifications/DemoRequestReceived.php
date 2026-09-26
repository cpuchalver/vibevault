<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DemoRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemoRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DemoRequest $demoRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $demoRequest = $this->demoRequest;

        return (new MailMessage)
            ->subject("Demande de démo : {$demoRequest->company}")
            ->replyTo($demoRequest->email, $demoRequest->name)
            ->line('**Contact :** '.$this->escape("{$demoRequest->name} <{$demoRequest->email}>"))
            ->line('**Structure :** '.$this->escape($demoRequest->company))
            ->line("**Profil :** {$demoRequest->role->getLabel()}")
            ->line('**Catalogue :** '.($demoRequest->catalogue_size?->getLabel() ?? 'Non précisé'))
            ->line('**Message :** '.$this->escape($demoRequest->message ?: 'Aucun'));
    }

    /**
     * Neutralise Markdown syntax in prospect-provided text so it cannot
     * inject links or formatting into the internal email.
     */
    protected function escape(string $text): string
    {
        return preg_replace('/([\\\\`*_{}\[\]()#+\-.!|<>~])/', '\\\\$1', $text) ?? '';
    }
}
