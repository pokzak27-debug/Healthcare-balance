<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_booking',
            'message' => $this->appointment->patient->fullName() . ' a pris un RDV le ' . $this->appointment->appointment_date->format('d/m/Y') . ' à ' . \Carbon\Carbon::parse($this->appointment->appointment_time)->format('H:i'),
            'url' => route('appointments.show', $this->appointment),
        ];
    }
}
