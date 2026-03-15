<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AvailableSlotsService;
use App\Services\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Formulaire de prise de rendez-vous en ligne (public).
     */
    public function index(Request $request): View
    {
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::performers()->get();
        $prestations = Prestation::active()->ordered()->get();
        $rooms = Room::active()->ordered()->get();

        return view('booking.index', compact('poles', 'practitioners', 'prestations', 'rooms'));
    }

    /**
     * Créneaux disponibles (AJAX).
     */
    public function slots(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'pole_id' => ['required', 'exists:poles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90,120'],
        ]);
        if (! empty($validated['prestation_id'])) {
            $p = Prestation::find($validated['prestation_id']);
            $validated['duration_minutes'] = $p->duration_minutes ?? 30;
        }

        $service = app(AvailableSlotsService::class);
        $times = $service->getAvailableTimes(
            $validated['date'],
            (int) $validated['pole_id'],
            ! empty($validated['user_id']) ? (int) $validated['user_id'] : null,
            (int) ($validated['duration_minutes'] ?? 30)
        );

        return response()->json(['times' => $times]);
    }

    /**
     * Enregistrement du RDV en ligne.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pole_id' => ['required', 'exists:poles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:23:00'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90,120'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $patient = Patient::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'is_active' => true,
            ]
        );

        $patient->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
        ]);

        $validated['patient_id'] = $patient->id;
        $validated['status'] = 'scheduled';
        if (! empty($validated['prestation_id'])) {
            $p = Prestation::find($validated['prestation_id']);
            $validated['duration_minutes'] = $p->duration_minutes;
        } else {
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        }
        unset($validated['first_name'], $validated['last_name'], $validated['email'], $validated['phone']);

        $appointment = Appointment::create($validated);

        $webPush = app(WebPushService::class);
        if ($webPush->isConfigured()) {
            $webPush->sendToAllStaff(
                'Nouveau rendez-vous en ligne',
                $patient->fullName() . ' - ' . $appointment->appointment_date->format('d/m/Y') . ' à ' . $appointment->appointment_time,
                route('appointments.show', $appointment)
            );
        }

        $users = \App\Models\User::where('is_active', true)->whereHas('role')->get();
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\NewBookingNotification($appointment));
        }

        return redirect()->route('booking.index')
            ->with('success', "Rendez-vous confirmé pour le {$appointment->appointment_date->format('d/m/Y')} à {$appointment->appointment_time}. Un email de rappel vous sera envoyé la veille.");
    }
}
