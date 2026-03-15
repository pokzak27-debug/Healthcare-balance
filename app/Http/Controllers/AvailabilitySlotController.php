<?php

namespace App\Http\Controllers;

use App\Models\AvailabilitySlot;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilitySlotController extends Controller
{
    /**
     * Liste des créneaux de disponibilité.
     */
    public function index(Request $request): View
    {
        $query = AvailabilitySlot::with(['user', 'pole']);

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($poleId = $request->get('pole_id')) {
            $query->where('pole_id', $poleId);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $slots = $query->orderBy('user_id')->orderBy('pole_id')->orderBy('day_of_week')->orderBy('start_time')->paginate(20)->withQueryString();
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::practitioners()->get();

        return view('availability-slots.index', compact('slots', 'poles', 'practitioners'));
    }

    /**
     * Formulaire de création.
     */
    public function create(): View
    {
        $slot = new AvailabilitySlot();
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::practitioners()->get();

        return view('availability-slots.create', compact('slot', 'poles', 'practitioners'));
    }

    /**
     * Enregistrement d'un nouveau créneau.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'pole_id' => ['nullable', 'exists:poles,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['nullable', 'integer', 'in:15,30,45,60'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['user_id']) && empty($validated['pole_id'])) {
            return back()->withErrors(['user_id' => 'Choisir un praticien ou un pôle.'])->withInput();
        }

        $validated['slot_minutes'] = $validated['slot_minutes'] ?? 30;
        $validated['is_active'] = $request->boolean('is_active', true);

        AvailabilitySlot::create($validated);

        return redirect()->route('availability-slots.index')->with('success', 'Créneau ajouté.');
    }

    /**
     * Détail d'un créneau.
     */
    public function show(AvailabilitySlot $availabilitySlot): View
    {
        $availabilitySlot->load(['user', 'pole']);

        return view('availability-slots.show', compact('availabilitySlot'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(AvailabilitySlot $availabilitySlot): View
    {
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::practitioners()->get();

        return view('availability-slots.edit', compact('availabilitySlot', 'poles', 'practitioners'));
    }

    /**
     * Mise à jour d'un créneau.
     */
    public function update(Request $request, AvailabilitySlot $availabilitySlot): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'pole_id' => ['nullable', 'exists:poles,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['nullable', 'integer', 'in:15,30,45,60'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['user_id']) && empty($validated['pole_id'])) {
            return back()->withErrors(['user_id' => 'Choisir un praticien ou un pôle.'])->withInput();
        }

        $validated['slot_minutes'] = $validated['slot_minutes'] ?? 30;
        $validated['is_active'] = $request->boolean('is_active', true);

        $availabilitySlot->update($validated);

        return redirect()->route('availability-slots.index')->with('success', 'Créneau mis à jour.');
    }

    /**
     * Suppression d'un créneau.
     */
    public function destroy(AvailabilitySlot $availabilitySlot): RedirectResponse
    {
        $availabilitySlot->delete();

        return redirect()->route('availability-slots.index')->with('success', 'Créneau supprimé.');
    }
}
