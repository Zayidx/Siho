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

class FnbPaymentStatusUpdatedAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public FnbOrder $order;

    public string $status;

    public function __construct(FnbOrder $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Status Pembayaran F&B Diperbarui - #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.fnb-payment-status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

