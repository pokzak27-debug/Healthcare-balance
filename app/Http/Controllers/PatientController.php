<?php

/**
 * Propriété de ZAKARIAE BOUKHROUF - Projet HB Smart Clinic.
 * Tous droits d'utilisation réservés à ZAKARIAE BOUKHROUF pour le cabinet Healthcare Balance.
 */

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Liste des patients.
     */
    public function index(Request $request): View
    {
        $allowedSorts = ['first_name', 'last_name', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        $sort = $request->get('sort');
        $direction = $request->get('direction', 'asc');

        if (! in_array($sort, $allowedSorts)) {
            $sort = 'last_name';
        }
        if (! in_array($direction, $allowedDirections)) {
            $direction = 'asc';
        }

        $query = Patient::query();

        if ($search = trim($request->get('search', ''))) {
            $phoneDigits = preg_replace('/\D/', '', $search);
            $query->where(function ($q) use ($search, $phoneDigits) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                if (strlen($phoneDigits) >= 4) {
                    $q->orWhere('phone_digits', 'like', "%{$phoneDigits}%");
                }
            });
        }

        $patients = $query
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    /**
     * Formulaire de création.
     */
    public function create(): View
    {
        $patient = new Patient();
        return view('patients.create', compact('patient'));
    }

    /**
     * Enregistrement d'un nouveau patient.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'sexe' => ['nullable', 'string', 'in:M,F,autre'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $patient = Patient::create($validated);
        ActivityLogger::log('patient.created', "Patient créé : {$patient->fullName()}", Patient::class, $patient->id, null, $validated);

        return redirect()->route('patients.index')
            ->with('success', 'Patient créé avec succès.');
    }

    /**
     * Détail d'un patient. Staff → fiche, Docteur/super_admin → dossier complet.
     */
    public function show(Patient $patient): View|RedirectResponse
    {
        if (! can_access_dossier()) {
            return redirect()->route('patients.fiche', $patient);
        }

        $patient->load([
            'appointments' => fn ($q) => $q->with(['pole', 'user'])->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc'),
            'medicalEntries' => fn ($q) => $q->with('user'),
            'diagnosticFishes' => fn ($q) => $q->with('user')->orderByDesc('date_examen')->orderByDesc('created_at'),
        ]);

        return view('patients.show', compact('patient'));
    }

    /**
     * Fiche patient (staff) — identité, coordonnées, prochains RDV uniquement.
     */
    public function fiche(Patient $patient): View
    {
        $patient->load(['appointments' => fn ($q) => $q->with(['pole', 'user'])
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled'])
            ->orderBy('appointment_date')->orderBy('appointment_time')
        ]);
        $upcomingAppointments = $patient->appointments;

        return view('patients.fiche', compact('patient', 'upcomingAppointments'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Mise à jour d'un patient.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'sexe' => ['nullable', 'string', 'in:M,F,autre'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = $patient->toArray();
        $patient->update($validated);
        ActivityLogger::log('patient.updated', "Patient modifié : {$patient->fullName()}", Patient::class, $patient->id, $old, $validated);

        return redirect()->route('patients.index')
            ->with('success', 'Patient mis à jour.');
    }

    /**
     * Export PDF de la fiche patient.
     */
    public function exportPdf(Patient $patient)
    {
        $patient->load(['appointments' => fn ($q) => $q->with(['pole', 'user'])->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc')]);
        $appointments = $patient->appointments;

        $pdf = Pdf::loadView('pdf.patient', compact('patient', 'appointments'));

        return $pdf->download('fiche-patient-'.str_replace(' ', '-', $patient->fullName()).'-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Suppression d'un patient (docteur / super_admin uniquement).
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403, 'Accès réservé au médecin.');
        }
        $name = $patient->fullName();
        $id = $patient->id;
        $patient->delete();
        ActivityLogger::log('patient.deleted', "Patient supprimé : {$name}", Patient::class, $id);

        return redirect()->route('patients.index')
            ->with('success', 'Patient supprimé.');
    }
}
