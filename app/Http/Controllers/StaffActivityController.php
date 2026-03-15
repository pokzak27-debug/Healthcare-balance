<?php

namespace App\Http\Controllers;

use App\Models\StaffActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isDoctor = in_array($user->role?->slug ?? '', ['super_admin', 'doctor'], true);

        if ($isDoctor) {
            $query = StaffActivity::with('user')->orderByDesc('created_at');
        } else {
            $query = StaffActivity::with('user')->where('user_id', $user->id)->orderByDesc('created_at');
        }

        $activities = $query->paginate(20)->withQueryString();

        return view('staff-activities.index', compact('activities', 'isDoctor'));
    }

    public function create(): View
    {
        return view('staff-activities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:info,alert,report'],
        ]);
        $validated['user_id'] = auth()->id();

        StaffActivity::create($validated);

        return redirect()->route('staff-activities.index')->with('success', 'Activité enregistrée.');
    }

    public function markRead(StaffActivity $staffActivity): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403);
        }
        $staffActivity->update(['read_by_doctor' => true]);

        return back()->with('success', 'Marqué comme lu.');
    }

    public function markAllRead(): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403);
        }
        StaffActivity::where('read_by_doctor', false)->update(['read_by_doctor' => true]);

        return back()->with('success', 'Toutes les activités marquées comme lues.');
    }
}
