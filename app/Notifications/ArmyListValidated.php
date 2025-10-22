<?php

namespace App\Notifications;

use App\Models\ArmyList;
use App\Models\Tournament;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArmyListValidated extends Notification
{
    protected $armyList;
    protected $tournament;

    public function __construct(ArmyList $armyList, Tournament $tournament)
    {
        $this->armyList = $armyList;
        $this->tournament = $tournament;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre inscription au tournoi ' . $this->tournament->name . ' a été acceptée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Félicitations \! Votre liste d\'armée a été validée pour le tournoi **' . $this->tournament->name . '**.')
            ->line('**Détails de votre inscription :**')
            ->line('- **Faction** : ' . ($this->armyList->faction->name ?? 'Inconnue'))
            ->line('- **Détachement** : ' . $this->armyList->detachment)
            ->line('- **Points** : ' . $this->armyList->points . ' pts')
            ->line('- **Format du tournoi** : ' . ucfirst($this->tournament->format))
            ->line('- **Taille d\'armée** : ' . $this->tournament->getArmySizeFormatted())
            ->action('Voir le tournoi', route('tournaments.show', $this->tournament))
            ->line('Les matchs seront générés automatiquement au fur et à mesure des validations.')
            ->line('Merci de votre participation \!');
    }
}
