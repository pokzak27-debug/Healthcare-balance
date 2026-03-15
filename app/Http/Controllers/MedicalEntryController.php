<?php

namespace App\Http\Controllers;

use App\Models\MedicalEntry;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalEntryController extends Controller
{
    public function create(Request $request): View
    {
        $patient = Patient::findOrFail($request->get('patient_id'));
        $appointmentId = $request->get('appointment_id');

        return view('medical-entries.create', compact('patient', 'appointmentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'type' => ['required', 'in:diagnostic,maladie,traitement,note_clinique'],
            'content' => ['required', 'string', 'max:10000'],
            'entry_date' => ['nullable', 'date'],
        ]);
        $validated['user_id'] = auth()->id();

        MedicalEntry::create($validated);

        $redirect = route('patients.show', $validated['patient_id']);

        return redirect($redirect)->with('success', 'Entrée médicale enregistrée.');
    }

    public function edit(MedicalEntry $medicalEntry): View
    {
        $medicalEntry->load('patient');

        return view('medical-entries.edit', compact('medicalEntry'));
    }

    public function update(Request $request, MedicalEntry $medicalEntry): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:diagnostic,maladie,traitement,note_clinique'],
            'content' => ['required', 'string', 'max:10000'],
            'entry_date' => ['nullable', 'date'],
        ]);

        $medicalEntry->update($validated);

        return redirect()->route('patients.show', $medicalEntry->patient_id)->with('success', 'Entrée mise à jour.');
    }

    public function destroy(MedicalEntry $medicalEntry): RedirectResponse
    {
        $patientId = $medicalEntry->patient_id;
        $medicalEntry->delete();

        return redirect()->route('patients.show', $patientId)->with('success', 'Entrée supprimée.');
    }
}
