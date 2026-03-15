<?php

namespace App\Http\Controllers;

use App\Models\DiagnosticFish;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosticFishController extends Controller
{
    /** Liste des fiches diagnostic du patient (accès docteur uniquement). */
    public function index(Patient $patient): View
    {
        $patient->load(['diagnosticFishes' => fn ($q) => $q->with('user')->orderByDesc('date_examen')->orderByDesc('created_at')]);

        return view('diagnostic-fishes.index', compact('patient'));
    }

    public function create(Patient $patient): View
    {
        return view('diagnostic-fishes.create', compact('patient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'examen_clinique' => ['nullable', 'string', 'max:10000'],
            'examen_subjectif' => ['nullable', 'string', 'max:10000'],
            'examen_objectif' => ['nullable', 'string', 'max:10000'],
            'examen_dynamique' => ['nullable', 'string', 'max:10000'],
            'examen_statique' => ['nullable', 'string', 'max:10000'],
            'diagnostic' => ['nullable', 'string', 'max:5000'],
            'protocole' => ['nullable', 'string', 'max:10000'],
            'date_examen' => ['nullable', 'date'],
        ]);
        $validated['user_id'] = auth()->id();
        if (empty($validated['date_examen'])) {
            $validated['date_examen'] = now();
        }

        DiagnosticFish::create($validated);

        return redirect()->route('patients.show', $validated['patient_id'])
            ->with('success', 'Fiche de diagnostic enregistrée.');
    }

    public function show(DiagnosticFish $diagnosticFish): View
    {
        $diagnosticFish->load(['patient', 'user']);

        return view('diagnostic-fishes.show', compact('diagnosticFish'));
    }

    public function edit(DiagnosticFish $diagnosticFish): View
    {
        $diagnosticFish->load('patient');

        return view('diagnostic-fishes.edit', compact('diagnosticFish'));
    }

    public function update(Request $request, DiagnosticFish $diagnosticFish): RedirectResponse
    {
        $validated = $request->validate([
            'examen_clinique' => ['nullable', 'string', 'max:10000'],
            'examen_subjectif' => ['nullable', 'string', 'max:10000'],
            'examen_objectif' => ['nullable', 'string', 'max:10000'],
            'examen_dynamique' => ['nullable', 'string', 'max:10000'],
            'examen_statique' => ['nullable', 'string', 'max:10000'],
            'diagnostic' => ['nullable', 'string', 'max:5000'],
            'protocole' => ['nullable', 'string', 'max:10000'],
            'date_examen' => ['nullable', 'date'],
        ]);

        $diagnosticFish->update($validated);

        return redirect()->route('patients.show', $diagnosticFish->patient)
            ->with('success', 'Fiche de diagnostic mise à jour.');
    }

    public function destroy(DiagnosticFish $diagnosticFish): RedirectResponse
    {
        $patientId = $diagnosticFish->patient_id;
        $diagnosticFish->delete();

        return redirect()->route('diagnostic-fishes.index', ['patient' => $patientId])
            ->with('success', 'Fiche de diagnostic supprimée.');
    }
}
