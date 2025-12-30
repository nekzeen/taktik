<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecurityWeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $checks,
        public array $commands,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rapport sécurité hebdomadaire',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.security-weekly-report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
