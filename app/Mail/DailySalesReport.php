<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Carbon $date,
        public array $salesData,
        public float $totalRevenue,
        public int $totalQuantity,
        public int $orderCount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Sales Report - ' . $this->date->format('F j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-sales-report',
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
