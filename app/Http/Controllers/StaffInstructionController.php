<?php

namespace App\Http\Controllers;

use App\Models\StaffInstruction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffInstructionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isDoctor = in_array($user->role?->slug ?? '', ['super_admin', 'doctor'], true);

        if ($isDoctor) {
            $query = StaffInstruction::with(['fromUser', 'toUser', 'patient', 'room', 'prestation'])
                ->where('from_user_id', $user->id);
        } else {
            $query = StaffInstruction::with(['fromUser', 'toUser', 'patient', 'room', 'prestation'])
                ->where(function ($q) use ($user) {
                    $q->whereNull('to_user_id')->orWhere('to_user_id', $user->id);
                });
        }

        $instructions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('staff-instructions.index', compact('instructions', 'isDoctor'));
    }

    public function create(): View
    {
        if (! can_access_dossier()) {
            abort(403, 'Réservé au médecin.');
        }
        $staffUsers = User::whereHas('role', fn ($q) => $q->where('slug', 'staff'))->where('is_active', true)->orderBy('name')->get();

        return view('staff-instructions.create', compact('staffUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403, 'Réservé au médecin.');
        }

        $validated = $request->validate([
            'to_user_id' => ['nullable', 'exists:users,id'],
            'instruction' => ['nullable', 'string', 'max:2000'],
            'patient_id' => ['nullable', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'prestation_ids' => ['nullable', 'array'],
            'prestation_ids.*' => ['integer', 'exists:prestations,id'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'voir_patient' => ['nullable', 'boolean'],
        ]);
        $validated['from_user_id'] = auth()->id();
        $validated['instruction'] = $validated['instruction'] ?? '';
        if (empty($validated['instruction']) && empty($validated['patient_id'])) {
            return back()->withErrors(['instruction' => 'Une consigne texte ou un patient est requis.']);
        }
        $validated['voir_patient'] = (bool) ($validated['voir_patient'] ?? false);
        $validated['prestation_ids'] = isset($validated['prestation_ids']) ? array_values(array_filter(array_map('intval', $validated['prestation_ids']))) : null;
        if (empty($validated['prestation_ids'])) {
            unset($validated['prestation_ids']);
        }

        StaffInstruction::create($validated);

        return redirect()->back()->with('success', 'Consigne envoyée.');
    }

    public function markRead(StaffInstruction $staffInstruction): RedirectResponse
    {
        $staffInstruction->update([
            'is_read' => true,
            'read_at' => now(),
            'read_by' => auth()->id(),
        ]);

        return back()->with('success', 'Consigne marquée comme lue.');
    }

    public function markDone(StaffInstruction $staffInstruction): RedirectResponse
    {
        $staffInstruction->update(['is_done' => true, 'done_at' => now()]);

        return back()->with('success', 'Consigne marquée comme effectuée.');
    }
}
