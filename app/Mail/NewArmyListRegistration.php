<?php

namespace App\Mail;

use App\Models\ArmyList;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewArmyListRegistration extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ArmyList $armyList,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle inscription au tournoi : ' . $this->armyList->tournament->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-army-list-registration',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
