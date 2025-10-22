<?php

namespace App\Notifications;

use App\Models\MatchAvailability;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchAvailabilityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $availability;
    protected $match;
    protected $player;

    /**
     * Create a new notification instance.
     */
    public function __construct(MatchAvailability $availability, TournamentMatch $match, User $player)
    {
        $this->availability = $availability;
        $this->match = $match;
        $this->player = $player;
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
        $tournament = $this->match->tournament;
        $availabilityText = $this->availability->getFormattedAvailability();
        
        $message = (new MailMessage)
            ->subject('🎯 Disponibilité pour votre match - ' . $tournament->name)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('**' . $this->player->name . '** a défini sa disponibilité pour votre match :')
            ->line('📅 **' . $availabilityText . '**');

        if ($this->availability->notes) {
            $message->line('💬 Note : ' . $this->availability->notes);
        }

        $message->line('🏆 **Tournoi :** ' . $tournament->name)
                ->line('🎲 **Round :** ' . $this->match->round);

        if ($this->match->table_number) {
            $message->line('📍 **Table :** ' . $this->match->table_number);
        }

        $message->action('Voir le match', route('tournaments.matches.index', $tournament))
                ->line('N\'oubliez pas de définir votre propre disponibilité pour faciliter l\'organisation du match !');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'tournament_id' => $this->match->tournament_id,
            'player_id' => $this->player->id,
            'availability_id' => $this->availability->id,
        ];
    }
}
