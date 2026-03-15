<?php

namespace App\Http\Controllers;

use App\Models\PractitionerAbsence;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PractitionerAbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $query = PractitionerAbsence::with('user')->orderBy('start_date', 'desc');
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        $absences = $query->paginate(20)->withQueryString();
        $practitioners = User::practitioners()->get();

        return view('practitioner-absences.index', compact('absences', 'practitioners'));
    }

    public function create(): View
    {
        $practitioners = User::practitioners()->get();

        return view('practitioner-absences.create', compact('practitioners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        PractitionerAbsence::create($validated);

        return redirect()->route('practitioner-absences.index')->with('success', 'Absence enregistrée.');
    }

    public function edit(PractitionerAbsence $practitionerAbsence): View
    {
        $practitioners = User::practitioners()->get();

        return view('practitioner-absences.edit', compact('practitionerAbsence', 'practitioners'));
    }

    public function update(Request $request, PractitionerAbsence $practitionerAbsence): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $practitionerAbsence->update($validated);

        return redirect()->route('practitioner-absences.index')->with('success', 'Absence mise à jour.');
    }

    public function destroy(PractitionerAbsence $practitionerAbsence): RedirectResponse
    {
        $practitionerAbsence->delete();

        return redirect()->route('practitioner-absences.index')->with('success', 'Absence supprimée.');
    }
}
