<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientScheduleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientScheduleController extends Controller
{
    /**
     * Affiche le calendrier patient + formulaire simple d’édition.
     */
    public function show(Patient $patient): View
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
            return $apt->appointment_date && $apt->appointment_time
                && ! $appointmentIdsInSchedule->has($apt->id);
        })->map(function ($apt) {
            return (object) [
                'date' => $apt->appointment_date,
                'time' => \Carbon\Carbon::parse($apt->appointment_time)->format('H:i'),
                'pole_label' => $apt->pole?->name,
            ];
        });

        $entries = $fromSchedule->concat($fromAppointments)
            ->sort(function ($a, $b) {
                $d = $a->date <=> $b->date;
                return $d !== 0 ? $d : strcmp($a->time ?? '', $b->time ?? '');
            })
            ->values();

        $hasEntries = $entries->isNotEmpty();

        $scheduleItemsForExport = $scheduleItems->map(fn (PatientScheduleItem $e) => (object) [
            'date' => $e->session_date,
            'time' => $e->session_time,
            'pole' => $e->pole,
        ]);
        $periodStart = $entries->min(fn ($i) => $i->date) ?: null;
        $periodEnd = $entries->max(fn ($i) => $i->date) ?: null;

        $canExport = $scheduleItems->isNotEmpty();

        return view('patients.schedule.show', compact('patient', 'entries', 'hasEntries', 'canExport', 'scheduleItemsForExport', 'periodStart', 'periodEnd'));
    }

    /**
     * Initialise le calendrier patient à partir des rendez-vous existants.
     */
    public function initialize(Patient $patient): RedirectResponse
    {
        $existing = PatientScheduleItem::where('patient_id', $patient->id)->count();
        if ($existing > 0) {
            return redirect()
                ->route('patients.schedule.show', $patient)
                ->with('info', 'Le calendrier patient est déjà initialisé. Vous pouvez le modifier manuellement.');
        }

        $appointments = Appointment::where('patient_id', $patient->id)
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        foreach ($appointments as $apt) {
            if (! $apt->appointment_date || ! $apt->appointment_time) {
                continue;
            }

            PatientScheduleItem::create([
                'patient_id' => $patient->id,
                'appointment_id' => $apt->id,
                'session_date' => $apt->appointment_date->format('Y-m-d'),
                'session_time' => \Carbon\Carbon::parse($apt->appointment_time)->format('H:i'),
                'pole' => $apt->pole?->name,
            ]);
        }

        return redirect()
            ->route('patients.schedule.show', $patient)
            ->with('success', 'Calendrier patient initialisé à partir des rendez-vous existants.');
    }

    /**
     * Met à jour le calendrier patient à partir du formulaire (liste de lignes).
     * Stratégie simple V1 : on supprime les anciennes lignes et on recrée toutes les lignes envoyées.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'entries' => ['nullable', 'array'],
            'entries.*.date' => ['nullable', 'date'],
            'entries.*.time' => ['nullable', 'date_format:H:i'],
            'entries.*.pole_label' => ['nullable', 'string', 'max:191'],
        ]);

        $rows = collect($data['entries'] ?? [])
            ->filter(fn ($row) => ! empty($row['date']) && ! empty($row['time']))
            ->map(fn ($row) => [
                'date' => $row['date'],
                'time' => $row['time'],
                'pole' => $row['pole_label'] ?? null,
            ])
            ->values();

        PatientScheduleItem::where('patient_id', $patient->id)->delete();

        foreach ($rows as $row) {
            PatientScheduleItem::create([
                'patient_id' => $patient->id,
                'session_date' => $row['date'],
                'session_time' => $row['time'],
                'pole' => $row['pole'],
            ]);
        }

        return redirect()
            ->route('patients.schedule.show', $patient)
            ->with('success', 'Calendrier patient mis à jour.');
    }

    /**
     * Export PDF : document simple pour le patient (date, heure, pôle).
     */
    public function exportPdf(Patient $patient)
    {
        $entries = PatientScheduleItem::where('patient_id', $patient->id)
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $pdf = Pdf::loadView('pdf.patient_schedule', compact('patient', 'entries'));

        return $pdf->download('calendrier-patient-' . str_replace(' ', '-', $patient->fullName()) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Word : même contenu, version .doc simple.
     */
    public function exportWord(Patient $patient)
    {
        $entries = PatientScheduleItem::where('patient_id', $patient->id)
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $html = view('word.patient_schedule', compact('patient', 'entries'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="calendrier-patient-' . str_replace(' ', '-', $patient->fullName()) . '-' . now()->format('Y-m-d') . '.doc"',
        ]);
    }
}

