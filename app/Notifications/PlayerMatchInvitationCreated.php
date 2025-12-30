<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerMatchInvitationCreated extends Notification
{
    use Queueable;

    public $playerMatch;
    public $invitation;
    public $invitedBy;

    public function __construct($playerMatch, $invitation, $invitedBy)
    {
        $this->playerMatch = $playerMatch;
        $this->invitation = $invitation;
        $this->invitedBy = $invitedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation à un match')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous avez été invité à un match par ' . $this->invitedBy->name . '.')
            ->line('**Détails du match :**')
            ->line('Type : ' . $this->playerMatch->getTypeLabel())
            ->line('Points : ' . $this->playerMatch->army_points . ' pts')
            ->line('Localisation : ' . $this->playerMatch->getLocationDisplay())
            ->line('Disponibilité : ' . $this->playerMatch->getAvailabilityDisplay())
            ->when($this->invitation->message, function ($mail) {
                return $mail->line('**Message :** ' . $this->invitation->message);
            })
            ->action('Voir le match', route('player-matches.show', $this->playerMatch))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
