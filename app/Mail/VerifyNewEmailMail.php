<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyNewEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $verifyUrl;
    public string $name;

    public function __construct(string $verifyUrl, string $name)
    {
        $this->verifyUrl = $verifyUrl;
        $this->name = $name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Verifikasi Email Baru Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.verify-new-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

