<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentApiController extends Controller
{
    /**
     * Liste des rendez-vous (paginée).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::with(['patient', 'pole', 'user']);

        if ($poleId = $request->get('pole_id')) {
            $query->where('pole_id', $poleId);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('appointment_date', '<=', $dateTo);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json($appointments);
    }

    /**
     * Affiche un rendez-vous.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $appointment->load(['patient', 'pole', 'user']);

        return response()->json($appointment);
    }

    /**
     * Crée un rendez-vous.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'pole_id' => ['required', 'exists:poles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90'],
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;

        $appointment = Appointment::create($validated);
        $appointment->load(['patient', 'pole', 'user']);

        return response()->json($appointment, 201);
    }

    /**
     * Met à jour un rendez-vous.
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['sometimes', 'exists:patients,id'],
            'pole_id' => ['sometimes', 'exists:poles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['sometimes', 'date'],
            'appointment_time' => ['sometimes', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90'],
            'status' => ['sometimes', 'in:scheduled,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $appointment->update($validated);
        $appointment->load(['patient', 'pole', 'user']);

        return response()->json($appointment);
    }

    /**
     * Supprime un rendez-vous.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}
