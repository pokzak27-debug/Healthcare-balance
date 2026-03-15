<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientApiController extends Controller
{
    /**
     * Liste des patients (paginée).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Patient::query();

        if ($search = trim($request->get('search'))) {
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

        $patients = $query->orderBy('last_name')->orderBy('first_name')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json($patients);
    }

    /**
     * Affiche un patient.
     */
    public function show(Patient $patient): JsonResponse
    {
        $patient->load('appointments.pole', 'appointments.user');

        return response()->json($patient);
    }

    /**
     * Crée un patient.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'sexe' => ['nullable', 'in:M,F,autre'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $patient = Patient::create($validated);

        return response()->json($patient, 201);
    }

    /**
     * Met à jour un patient.
     */
    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'sexe' => ['nullable', 'in:M,F,autre'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    /**
     * Supprime un patient.
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return response()->json(null, 204);
    }
}
