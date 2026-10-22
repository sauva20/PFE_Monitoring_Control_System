<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $jadwals;

    public $pin;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $jadwals, ?string $pin = null)
    {
        $this->user = $user;
        $this->jadwals = $jadwals;
        $this->pin = $pin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penugasan & Batas Deadline Inspeksi APAR - EHSS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inspection_assigned',
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
