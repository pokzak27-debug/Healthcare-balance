<?php

namespace App\Services\PatientSchedule;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PatientScheduleDataService
{
    /**
     * Récupère le calendrier patient (indépendant) et, en complément, les rendez-vous réels non encore copiés.
     * Utilisé pour l'affichage/édition (pré-remplissage).
     */
    public function buildForEditing(Patient $patient): Collection
    {
        $scheduleItems = PatientScheduleItem::where('patient_id', $patient->id)
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $appointments = Appointment::where('patient_id', $patient->id)
            ->whereIn('status', ['scheduled', 'completed'])
            ->with('pole')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $appointmentIdsInSchedule = $scheduleItems->pluck('appointment_id')->filter()->unique()->flip();

        $fromSchedule = $scheduleItems->map(fn (PatientScheduleItem $e) => (object) [
            'date' => $e->session_date,
            'time' => $e->session_time,
            'pole_label' => $e->pole,
        ]);

        $fromAppointments = $appointments->filter(function ($apt) use ($appointmentIdsInSchedule) {
            return $apt->appointment_date && $apt->appointment_time && ! $appointmentIdsInSchedule->has($apt->id);
        })->map(function ($apt) {
            return (object) [
                'date' => $apt->appointment_date,
                'time' => Carbon::parse($apt->appointment_time)->format('H:i'),
                'pole_label' => $apt->pole?->name,
            ];
        });

        return $fromSchedule->concat($fromAppointments)
            ->sort(function ($a, $b) {
                $d = $a->date <=> $b->date;
                return $d !== 0 ? $d : strcmp($a->time ?? '', $b->time ?? '');
            })
            ->values();
    }

    /**
     * Données strictes pour documents (PDF/DOCX) : uniquement calendrier patient enregistré.
     */
    public function buildForDocuments(Patient $patient): PatientScheduleData
    {
        $items = PatientScheduleItem::where('patient_id', $patient->id)
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get()
            ->map(fn (PatientScheduleItem $e) => (object) [
                'date' => $e->session_date,
                'time' => $e->session_time,
                'pole' => $e->pole,
            ]);

        $periodStart = $items->min(fn ($i) => $i->date) ?: null;
        $periodEnd = $items->max(fn ($i) => $i->date) ?: null;

        return new PatientScheduleData(
            items: $items,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            totalSessions: $items->count()
        );
    }
}

