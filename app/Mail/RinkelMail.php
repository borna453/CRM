<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RinkelMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $callDetails;
    public function __construct(string $name, string $callDetails)
    {
        $this->name = $name;
        $this->callDetails = $callDetails;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rinkel incoming event',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rinkel',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
