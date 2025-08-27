<?php

namespace App\Mail;

use App\Models\Bills;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Bills $bill;

    public function __construct(Bills $bill)
    {
        $this->bill = $bill;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Bukti Pembayaran Ditolak - Invoice #'.$this->bill->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.payment-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

