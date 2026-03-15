<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {
        $this->appointment->load(['patient', 'pole', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : Rendez-vous demain - Healthcare Balance',
            from: config('mail.from.address'),
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
            text: 'emails.appointment-reminder-text',
        );
    }
}
