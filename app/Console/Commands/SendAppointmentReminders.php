<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentReminderStaffNotification;
use App\Services\SmsService;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
                            {--days=1 : Nombre de jours avant le rendez-vous}
                            {--dry-run : Afficher sans envoyer}
                            {--sms : Envoyer aussi par SMS (si Twilio configuré)}
                            {--push : Notifier le staff par Web Push}';

    protected $description = 'Envoie les rappels par email (et optionnellement SMS) aux patients';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $sendSms = $this->option('sms');

        $targetDate = now()->addDays($days)->toDateString();

        $appointments = Appointment::with(['patient', 'pole', 'user'])
            ->whereDate('appointment_date', $targetDate)
            ->where('status', 'scheduled')
            ->get();

        $toSend = $appointments->filter(fn ($apt) => $apt->patient && filter_var($apt->patient->email, FILTER_VALIDATE_EMAIL));

        $noEmail = $appointments->count() - $toSend->count();
        if ($noEmail > 0) {
            $this->warn("{$noEmail} rendez-vous sans email patient (non envoyés).");
        }

        if ($toSend->isEmpty()) {
            $this->info("Aucun rappel à envoyer pour le {$targetDate}.");

            return 0;
        }

        if ($dryRun) {
            $this->info("Dry-run : {$toSend->count()} email(s) seraient envoyés pour le {$targetDate}");
            foreach ($toSend as $apt) {
                $this->line("  - {$apt->patient->fullName()} ({$apt->patient->email}) - " . \Carbon\Carbon::parse($apt->appointment_time)->format('H:i'));
            }

            return 0;
        }

        $smsService = app(SmsService::class);
        $webPush = app(WebPushService::class);
        $sent = 0;
        $smsSent = 0;
        $pushSent = 0;

        if ($this->option('push') && $webPush->isConfigured()) {
            $formattedDate = \Carbon\Carbon::parse($targetDate)->locale('fr')->translatedFormat('d/m/Y');
            $pushSent = $webPush->sendToAllStaff(
                'Rappel RDV demain',
                $toSend->count() . ' rendez-vous prévu(s) pour le ' . $formattedDate,
                route('appointments.index')
            );
        }

        $staffUsers = User::where('is_active', true)->whereHas('role')->get();
        foreach ($staffUsers as $user) {
            $user->notify(new AppointmentReminderStaffNotification($toSend->count(), \Carbon\Carbon::parse($targetDate)->locale('fr')->translatedFormat('d/m/Y')));
        }

        foreach ($toSend as $appointment) {
            try {
                Mail::to($appointment->patient->email)->send(new AppointmentReminderMail($appointment));
                $sent++;
                $this->info("Email envoyé à {$appointment->patient->fullName()} ({$appointment->patient->email})");
            } catch (\Throwable $e) {
                $this->error("Erreur email pour {$appointment->patient->email} : {$e->getMessage()}");
            }
            if ($sendSms && $appointment->patient->phone) {
                $msg = "Rappel RDV Healthcare Balance: " . $appointment->appointment_date->format('d/m/Y')
                    . " à " . \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')
                    . ". " . ($appointment->pole?->name ?? '');
                if ($smsService->send($appointment->patient->phone, $msg)) {
                    $smsSent++;
                    $this->info("  SMS envoyé à {$appointment->patient->phone}");
                }
            }
        }

        $pushInfo = $pushSent > 0 ? ", {$pushSent} push" : '';
        $this->info("{$sent} email(s), {$smsSent} SMS{$pushInfo} envoyé(s) pour le {$targetDate}.");

        return 0;
    }
}
