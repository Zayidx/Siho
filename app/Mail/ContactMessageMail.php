<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class ContactMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $text;
    public ?string $subjectText;
    public ?string $phone;

    public function __construct(string $name, string $email, string $text, ?string $subjectText = null, ?string $phone = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->text = $text;
        $this->subjectText = $subjectText;
        $this->phone = $phone;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: ($this->subjectText ? ('[Kontak] '.$this->subjectText) : 'Pesan Kontak Baru dari '.$this->name),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.public.contact-message',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
