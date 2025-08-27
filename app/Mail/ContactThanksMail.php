<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class ContactThanksMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Terima kasih telah menghubungi kami',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.public.contact-thanks',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
