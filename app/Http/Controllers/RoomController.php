<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoomController extends Controller
{
    /** Liste des salles. */
    public function index(): View
    {
        $rooms = Room::ordered()->withCount('machines')->get();

        return view('rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $room = new Room;

        return view('rooms.create', compact('room'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;
        $validated['sort_order'] ??= 0;

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Salle créée.');
    }

    public function show(Room $room): View
    {
        $room->load(['machines' => fn ($q) => $q->ordered()]);

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'in:free,occupied,cleaning'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', $room->is_active);
        $validated['sort_order'] ??= $room->sort_order;
        $validated['status'] ??= $room->status ?? 'free';

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Salle mise à jour.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->appointments()->exists()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Impossible de supprimer : des rendez-vous utilisent cette salle.');
        }
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Salle supprimée.');
    }

    /** Mise à jour rapide du statut (staff/doctor). */
    public function updateStatus(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:free,occupied,cleaning'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'prestation_id' => ['nullable', 'integer', 'exists:prestations,id'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
        ]);
        $oldStatus = $room->status ?? 'free';

        if ($validated['status'] === 'free') {
            $validated['current_appointment_id'] = null;
            $validated['current_patient_id'] = null;
            $validated['current_prestation_id'] = null;
            $validated['current_machine_id'] = null;
            $validated['occupied_started_at'] = null;
        } elseif ($validated['status'] === 'occupied') {
            $validated['occupied_started_at'] = now();
            if (! empty($validated['patient_id'] ?? null)) {
                $validated['current_patient_id'] = $validated['patient_id'];
            }
            if (! empty($validated['prestation_id'] ?? null)) {
                $validated['current_prestation_id'] = $validated['prestation_id'];
            }
            if (! empty($validated['machine_id'] ?? null)) {
                $machine = \App\Models\Machine::where('room_id', $room->id)->find($validated['machine_id']);
                $validated['current_machine_id'] = $machine?->id;
            }
            $appointment = null;
            if (! empty($validated['appointment_id'] ?? null)) {
                $appointment = \App\Models\Appointment::where('room_id', $room->id)
                    ->whereDate('appointment_date', now()->toDateString())
                    ->whereIn('status', ['scheduled', 'completed'])
                    ->find($validated['appointment_id']);
            }
            if (! $appointment) {
                $appointment = \App\Models\Appointment::where('room_id', $room->id)
                    ->whereDate('appointment_date', now()->toDateString())
                    ->whereIn('status', ['scheduled', 'completed'])
                    ->orderBy('appointment_time')
                    ->first();
            }
            $validated['current_appointment_id'] = $appointment?->id;
            if ($appointment && empty($validated['current_patient_id'])) {
                $validated['current_patient_id'] = $appointment->patient_id;
            }
            if ($appointment && empty($validated['current_prestation_id'])) {
                $validated['current_prestation_id'] = $appointment->prestation_id;
            }
        } elseif ($validated['status'] === 'cleaning') {
            $validated['occupied_started_at'] = null;
        }
        unset($validated['appointment_id'], $validated['patient_id'], $validated['prestation_id'], $validated['machine_id']);

        $room->update($validated);

        if (in_array($validated['status'], ['occupied', 'cleaning'], true)) {
            $doctors = \App\Models\User::whereHas('role', fn ($q) => $q->whereIn('slug', ['super_admin', 'doctor']))
                ->where('is_active', true)
                ->get();
            foreach ($doctors as $user) {
                $user->notify(new \App\Notifications\RoomStatusChangedNotification(
                    $room,
                    $oldStatus,
                    $validated['status'],
                    auth()->user()?->name
                ));
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }
}
