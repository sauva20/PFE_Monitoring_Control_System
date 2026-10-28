<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $jenis;

    public $tanggal;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $jenis, $tanggal)
    {
        $this->user = $user->withoutRelations()->load(['gedungs', 'lokasis.gedung']);
        $this->jenis = $jenis;
        $this->tanggal = $tanggal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Deadline Inspeksi Bulanan APAR - EHSS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inspection-reminder',
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
