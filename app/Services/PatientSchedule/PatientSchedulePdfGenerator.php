<?php

namespace App\Services\PatientSchedule;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientSchedulePdfGenerator
{
    public function generate(Patient $patient, PatientScheduleData $data, ?string $diagnosticSummary): string
    {
        $pdf = Pdf::loadView('pdf.patient_schedule', [
            'patient' => $patient,
            'entries' => $data->items->map(function ($i) {
                // Adapter au template PDF actuel: il attend session_date/session_time/pole
                return (object) [
                    'session_date' => $i->date,
                    'session_time' => $i->time,
                    'pole' => $i->pole,
                ];
            }),
            'diagnosticSummary' => $diagnosticSummary,
            'periodStart' => $data->periodStart,
            'periodEnd' => $data->periodEnd,
            'totalTreatmentDays' => $data->totalTreatmentDays(),
            'totalSessions' => $data->totalSessions,
        ]);

        return $pdf->output();
    }
}

