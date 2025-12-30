<?php

namespace App\Notifications;

use App\Models\Tournament;
use App\Models\TournamentInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TournamentInvitationCreated extends Notification
{
    use Queueable;

    public Tournament $tournament;
    public TournamentInvitation $invitation;
    public User $invitedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Tournament $tournament, TournamentInvitation $invitation, User $invitedBy)
    {
        $this->tournament = $tournament;
        $this->invitation = $invitation;
        $this->invitedBy = $invitedBy;
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
            ->subject('Invitation à un tournoi')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line($this->invitedBy->name . ' vous invite à rejoindre le tournoi "' . $this->tournament->name . '".')
            ->when($this->invitation->message, function ($mail) {
                return $mail->line('**Message :** ' . $this->invitation->message);
            })
            ->action('Voir le tournoi', route('tournaments.show', $this->tournament))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
