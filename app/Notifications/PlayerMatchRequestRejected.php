<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerMatchRequestRejected extends Notification
{
    use Queueable;

    public $playerMatch;
    public $playerMatchRequest;
    public $creator;

    public function __construct($playerMatch, $playerMatchRequest, $creator)
    {
        $this->playerMatch = $playerMatch;
        $this->playerMatchRequest = $playerMatchRequest;
        $this->creator = $creator;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande de participation a été refusée')
            ->greeting('Bonjour ' . $notifiable->name . '\!')
            ->line('Malheureusement, votre demande de participation au match a été refusée par ' . $this->creator->name . '.')
            ->line('')
            ->line('**Détails du match:**')
            ->line('Type: ' . $this->playerMatch->getTypeLabel())
            ->line('Points: ' . $this->playerMatch->army_points . ' pts')
            ->line('Localisation: ' . $this->playerMatch->getLocationDisplay())
            ->line('Disponibilité: ' . $this->playerMatch->getAvailabilityDisplay())
            ->line('')
            ->when($this->playerMatchRequest->creator_response, function ($mail) {
                return $mail->line('**Message du créateur:** ' . $this->playerMatchRequest->creator_response);
            })
            ->action('Voir les autres matchs', route('player-matches.index'))
            ->line('Merci d\'utiliser notre plateforme\!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
