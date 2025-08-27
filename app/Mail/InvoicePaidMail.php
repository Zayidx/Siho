<?php

namespace App\Mail;

use App\Models\Bills;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class InvoicePaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Bills $bill;
    public string $pdfBinary;

    public function __construct(Bills $bill, string $pdfBinary)
    {
        $this->bill = $bill;
        $this->pdfBinary = $pdfBinary;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Pembayaran Berhasil - Invoice #'.$this->bill->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.invoice-paid',
        );
    }

    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $this->pdfBinary, 'invoice-'.$this->bill->id.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
