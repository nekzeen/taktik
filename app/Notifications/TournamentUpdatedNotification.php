<?php

namespace App\Notifications;

use App\Models\Tournament;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

class TournamentUpdatedNotification extends Notification
{
    use Queueable;

    public Tournament $tournament;

    /**
     * @var array<string, array{before:mixed,after:mixed}>
     */
    public array $changes;

    public string $updatedByName;

    /**
     * @param array<string, array{before:mixed,after:mixed}> $changes
     */
    public function __construct(Tournament $tournament, array $changes, string $updatedByName)
    {
        $this->tournament = $tournament;
        $this->changes = $changes;
        $this->updatedByName = $updatedByName;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Modification du tournoi : ' . $this->tournament->name)
            ->line('**Informations actuelles du tournoi :**')
            ->line('Nom : ' . $this->tournament->name);

        if (!empty($this->tournament->description)) {
            $mail->line('Description :');

            $lines = preg_split('/\R/u', (string) $this->tournament->description);
            foreach ($lines as $line) {
                $mail->line($line !== '' ? $line : ' ');
            }
        }

        $mail
            ->line('Format : ' . $this->formatFormat($this->tournament->format))
            ->line('Taille d\'armée : ' . $this->tournament->getArmySizeFormatted())
            ->line('Date de début : ' . $this->formatDate($this->tournament->start_date))
            ->line('Date de fin : ' . $this->formatDate($this->tournament->end_date))
            ->line('Date limite d\'inscription : ' . $this->formatDateTime($this->tournament->registration_deadline))
            ->line('Nombre maximum de joueurs : ' . ($this->tournament->max_players ?? '-'))
            ->action('Voir le tournoi', route('tournaments.show', $this->tournament));

        return $mail;
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'name' => 'Nom',
            'description' => 'Description',
            'format' => 'Format',
            'army_size' => 'Taille d\'armée',
            'start_date' => 'Date de début',
            'end_date' => 'Date de fin',
            'registration_deadline' => 'Date limite d\'inscription',
            'max_players' => 'Nombre maximum de joueurs',
            default => $field,
        };
    }

    private function formatFieldValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (in_array($field, ['start_date', 'end_date'], true)) {
            return $this->formatDate($value);
        }

        if ($field === 'registration_deadline') {
            return $this->formatDateTime($value);
        }

        if ($field === 'format') {
            return $this->formatFormat((string) $value);
        }

        if ($field === 'army_size') {
            $tmp = clone $this->tournament;
            $tmp->army_size = (string) $value;
            return $tmp->getArmySizeFormatted();
        }

        return (string) $value;
    }

    private function formatFormat(?string $format): string
    {
        return match ($format) {
            'elimination' => 'Élimination',
            'swiss' => 'Suisse',
            'league' => 'Ligue',
            default => $format ?: '-',
        };
    }

    private function formatDate(mixed $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    private function formatDateTime(mixed $dateTime): string
    {
        if ($dateTime === null || $dateTime === '') {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($dateTime)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $dateTime;
        }
    }
}
