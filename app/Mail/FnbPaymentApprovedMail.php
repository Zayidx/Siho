<?php

namespace App\Mail;

use App\Models\FnbOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FnbPaymentApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public FnbOrder $order;

    public function __construct(FnbOrder $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Pembayaran F&B Diterima - Order #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.fnb.payment-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

