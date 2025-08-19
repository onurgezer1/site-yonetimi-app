<?php

namespace App\Mail;

use App\Models\Aidat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AidatReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Aidat $aidat,
        public bool $isOverdueReminder = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isOverdueReminder 
            ? "⚠️ Gecikmiş Aidat Hatırlatması - {$this->aidat->period}"
            : "📅 Aidat Hatırlatması - {$this->aidat->period}";
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.aidat-reminder',
            with: [
                'aidat' => $this->aidat,
                'isOverdueReminder' => $this->isOverdueReminder,
                'user' => $this->aidat->user,
                'site' => $this->aidat->site,
                'apartment' => $this->aidat->apartment,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}