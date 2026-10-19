<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AparExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $apars;

    public $targetDate;

    public $recipientName;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $apars, string $targetDate, string $recipientName = '')
    {
        $this->apars = $apars;
        $this->targetDate = $targetDate;
        $this->recipientName = $recipientName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $count = $this->apars->count();

        return new Envelope(
            subject: "Peringatan H-30: Terdapat {$count} APAR Kedaluwarsa",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.apar-expiry',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
