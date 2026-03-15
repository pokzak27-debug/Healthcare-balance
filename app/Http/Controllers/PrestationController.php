<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrestationController extends Controller
{
    /** Liste des prestations. */
    public function index(): View
    {
        $prestations = Prestation::with(['pole', 'room', 'machines'])->ordered()->get();

        return view('prestations.index', compact('prestations'));
    }

    public function create(): View
    {
        $prestation = new Prestation(['duration_minutes' => 30]);
        $poles = Pole::active()->ordered()->get();
        $rooms = Room::active()->ordered()->get();

        return view('prestations.create', compact('prestation', 'poles', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'pole_id' => ['nullable', 'exists:poles,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'room_required' => ['boolean'],
            'machine_ids' => ['nullable', 'array'],
            'machine_ids.*' => ['exists:machines,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['is_active'] = true;
        $validated['sort_order'] ??= 0;
        $validated['room_required'] = $request->boolean('room_required');
        $machineIds = $validated['machine_ids'] ?? [];
        unset($validated['machine_ids']);

        $prestation = Prestation::create($validated);
        $prestation->machines()->sync($machineIds);

        return redirect()->route('prestations.index')->with('success', 'Prestation créée.');
    }

    public function edit(Prestation $prestation): View
    {
        $poles = Pole::active()->ordered()->get();
        $rooms = Room::active()->ordered()->get();

        return view('prestations.edit', compact('prestation', 'poles', 'rooms'));
    }

    public function update(Request $request, Prestation $prestation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'pole_id' => ['nullable', 'exists:poles,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'room_required' => ['boolean'],
            'machine_ids' => ['nullable', 'array'],
            'machine_ids.*' => ['exists:machines,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', $prestation->is_active);
        $validated['sort_order'] ??= $prestation->sort_order;
        $validated['room_required'] = $request->boolean('room_required');
        $machineIds = $validated['machine_ids'] ?? [];
        unset($validated['machine_ids']);

        $prestation->update($validated);
        $prestation->machines()->sync($machineIds);

        return redirect()->route('prestations.index')->with('success', 'Prestation mise à jour.');
    }

    public function destroy(Prestation $prestation): RedirectResponse
    {
        if ($prestation->appointments()->exists()) {
            return redirect()->route('prestations.index')
                ->with('error', 'Impossible de supprimer : des rendez-vous utilisent cette prestation.');
        }
        $prestation->delete();

        return redirect()->route('prestations.index')->with('success', 'Prestation supprimée.');
    }
}
