<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerMatchRequestCreated extends Notification
{
    use Queueable;

    public $playerMatch;
    public $playerMatchRequest;
    public $requester;

    /**
     * Create a new notification instance.
     */
    public function __construct($playerMatch, $playerMatchRequest, $requester)
    {
        $this->playerMatch = $playerMatch;
        $this->playerMatchRequest = $playerMatchRequest;
        $this->requester = $requester;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle demande de participation à votre match')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line($this->requester->name . ' souhaite participer à votre match.')
            ->line('**Détails du match:**')
            ->line('Type: ' . $this->playerMatch->getTypeLabel())
            ->line('Points: ' . $this->playerMatch->army_points . ' pts')
            ->line('Localisation: ' . $this->playerMatch->getLocationDisplay())
            ->line('Disponibilité: ' . $this->playerMatch->getAvailabilityDisplay())
            ->line('')
            ->line('**Faction du demandeur:**')
            ->line($this->playerMatchRequest->faction . ' - ' . $this->playerMatchRequest->detachment)
            ->when($this->playerMatchRequest->message, function ($mail) {
                return $mail->line('**Message:** ' . $this->playerMatchRequest->message);
            })
            ->action('Voir la demande', route('player-matches.show', $this->playerMatch))
            ->line('Merci d\'utiliser notre plateforme!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
