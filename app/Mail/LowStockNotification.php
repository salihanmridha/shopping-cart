<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array $products
     */
    public function __construct(
        public array $products
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low Stock Alert - Action Required',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low-stock-notification',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
