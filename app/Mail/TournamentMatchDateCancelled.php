<?php

namespace App\Mail;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TournamentMatchDateCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tournament $tournament,
        public TournamentMatch $match,
        public User $proposedBy,
        public User $cancelledBy,
        public CarbonInterface $scheduledAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Date annulée : match dé-planifié - ' . $this->tournament->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tournament-match-date-cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
