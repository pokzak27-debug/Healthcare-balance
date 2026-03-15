<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorPatientConsigne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorPatientConsigneController extends Controller
{
    /** Marquer un patient comme arrivé à l'accueil (staff ou docteur) */
    public function markArrived(Appointment $appointment): RedirectResponse
    {
        if ($appointment->appointment_date->toDateString() !== now()->toDateString()) {
            return back()->with('error', 'Seuls les rendez-vous du jour peuvent être marqués.');
        }
        $appointment->update(['arrived_at' => now()]);

        return back()->with('success', 'Patient marqué comme arrivé.');
    }

    /** Créer une consigne (docteur) depuis le live cabinet */
    public function store(Request $request): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403);
        }

        $validated = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'doctor_sees_patient' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $validated['doctor_sees_patient'] = $request->boolean('doctor_sees_patient');
        $validated['from_user_id'] = auth()->id();
        $appointment = Appointment::findOrFail($validated['appointment_id']);
        $validated['patient_id'] = $appointment->patient_id;

        DoctorPatientConsigne::create($validated);

        return back()->with('success', 'Consigne envoyée à l\'assistante.');
    }

    /** Liste des consignes (assistante + docteur) */
    public function index(): View
    {
        $user = auth()->user();
        $isDoctor = can_access_dossier();

        if ($isDoctor) {
            $consignes = DoctorPatientConsigne::with(['appointment', 'patient', 'room', 'prestation', 'fromUser'])
                ->where('from_user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(20);
        } else {
            $consignes = DoctorPatientConsigne::with(['appointment', 'patient', 'room', 'prestation', 'fromUser'])
                ->whereIn('status', ['pending', 'acknowledged'])
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('doctor-patient-consignes.index', compact('consignes', 'isDoctor'));
    }

    /** Assistante confirme avoir lu la consigne */
    public function acknowledge(DoctorPatientConsigne $consigne): RedirectResponse
    {
        if ($consigne->status !== 'pending') {
            return back()->with('error', 'Consigne déjà traitée.');
        }
        $consigne->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ]);

        return back()->with('success', 'Consigne confirmée comme lue.');
    }

    /** Assistante signale la fin / tâche terminée */
    public function complete(DoctorPatientConsigne $consigne): RedirectResponse
    {
        if ($consigne->status === 'completed') {
            return back()->with('error', 'Consigne déjà terminée.');
        }
        $consigne->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Consigne marquée comme terminée.');
    }
}
