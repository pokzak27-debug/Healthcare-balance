<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MachineController extends Controller
{
    /** Liste des machines (par salle). */
    public function index(Request $request): View
    {
        $roomId = $request->get('room_id');
        $query = Machine::with('room')->ordered();
        if ($roomId) {
            $query->where('room_id', $roomId);
        }
        $machines = $query->get();
        $rooms = Room::active()->ordered()->get();

        return view('machines.index', compact('machines', 'rooms'));
    }

    public function create(Request $request): View
    {
        $machine = new Machine;
        $machine->room_id = $request->get('room_id');
        $rooms = Room::active()->ordered()->get();

        return view('machines.create', compact('machine', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['is_active'] = true;
        $validated['sort_order'] ??= 0;

        Machine::create($validated);

        return redirect()->route('machines.index', ['room_id' => $validated['room_id']])
            ->with('success', 'Machine ajoutée.');
    }

    public function edit(Machine $machine): View
    {
        $rooms = Room::active()->ordered()->get();

        return view('machines.edit', compact('machine', 'rooms'));
    }

    public function update(Request $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', $machine->is_active);
        $validated['sort_order'] ??= $machine->sort_order;

        $machine->update($validated);

        return redirect()->route('machines.index', ['room_id' => $validated['room_id']])
            ->with('success', 'Machine mise à jour.');
    }

    public function destroy(Machine $machine): RedirectResponse
    {
        $roomId = $machine->room_id;
        $machine->delete();

        return redirect()->route('machines.index', ['room_id' => $roomId])
            ->with('success', 'Machine supprimée.');
    }
}
