<?php

namespace App\Notifications;

use App\Models\PlayerAvailability;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerAvailabilityCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PlayerAvailability $availability;
    protected Tournament $tournament;
    protected User $player;

    public function __construct(PlayerAvailability $availability, Tournament $tournament, User $player)
    {
        $this->availability = $availability;
        $this->tournament = $tournament;
        $this->player = $player;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $availabilityText = $this->availability->getFormattedAvailability();

        $message = (new MailMessage)
            ->subject('📅 Disponibilité - ' . $this->tournament->name)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('**' . $this->player->name . '** a défini sa disponibilité pour le tournoi :')
            ->line('📅 **' . $availabilityText . '**');

        if ($this->availability->notes) {
            $message->line('💬 Note : ' . $this->availability->notes);
        }

        $message->action('Voir les matchs du tournoi', route('tournaments.matches.index', $this->tournament));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tournament_id' => $this->tournament->id,
            'player_id' => $this->player->id,
            'availability_id' => $this->availability->id,
        ];
    }
}
