<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPlayerRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user)
    {
        //
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
            ->subject('Nouveau joueur inscrit - ' . $this->user->name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un nouveau joueur vient de valider son inscription et a reçu le rôle "player".')
            ->line('**Nom :** ' . $this->user->name)
            ->line('**Email :** ' . $this->user->email)
            ->line('**Date d\'inscription :** ' . $this->user->created_at->format('d/m/Y H:i'))
            ->action('Voir le profil', route('filament.admin.resources.users.edit', $this->user->id))
            ->line('Merci,')
            ->line('L\'équipe Warhammer 40K');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ];
    }
}
