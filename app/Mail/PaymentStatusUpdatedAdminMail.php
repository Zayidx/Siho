<?php

namespace App\Mail;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdatedAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Bill $bill;

    public string $status;

    public function __construct(Bill $bill, string $status)
    {
        $this->bill = $bill;
        $this->status = $status;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Status Pembayaran Diperbarui - #'.$this->bill->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.payment-status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
