<?php

namespace App\Http\Controllers;

use App\Models\CabinetExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CabinetExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = CabinetExpense::with('user')->orderBy('expense_date', 'desc');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($request->filled('month')) {
            $parts = explode('-', $request->get('month'));
            if (count($parts) === 2) {
                $query->whereYear('expense_date', $parts[0])->whereMonth('expense_date', (int) $parts[1]);
            }
        }

        $expenses = $query->paginate(20)->withQueryString();

        $totalSuppliesThisMonth = (float) CabinetExpense::where('type', CabinetExpense::TYPE_SUPPLY)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $chargesToPay = (float) CabinetExpense::where('type', CabinetExpense::TYPE_CHARGE)
            ->whereNull('paid_at')
            ->sum('amount');

        return view('cabinet-expenses.index', compact('expenses', 'totalSuppliesThisMonth', 'chargesToPay'));
    }

    public function create(): View
    {
        $this->authorizeStaff();
        return view('cabinet-expenses.create', ['expense' => new CabinetExpense]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff();
        $validated = $request->validate([
            'type' => ['required', 'in:supply,charge'],
            'category' => ['required', Rule::in(array_merge(array_keys(CabinetExpense::SUPPLY_CATEGORIES), array_keys(CabinetExpense::CHARGE_CATEGORIES)))],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
        ]);
        $validated['user_id'] = auth()->id();
        CabinetExpense::create($validated);

        return redirect()->route('cabinet-expenses.index')->with('success', 'Enregistrement ajouté.');
    }

    public function edit(CabinetExpense $cabinetExpense): View
    {
        $this->authorizeStaff();
        return view('cabinet-expenses.edit', ['expense' => $cabinetExpense]);
    }

    public function update(Request $request, CabinetExpense $cabinetExpense): RedirectResponse
    {
        $this->authorizeStaff();
        $validated = $request->validate([
            'type' => ['required', 'in:supply,charge'],
            'category' => ['required', Rule::in(array_merge(array_keys(CabinetExpense::SUPPLY_CATEGORIES), array_keys(CabinetExpense::CHARGE_CATEGORIES)))],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
        ]);
        $cabinetExpense->update($validated);

        return redirect()->route('cabinet-expenses.index')->with('success', 'Enregistrement mis à jour.');
    }

    public function destroy(CabinetExpense $cabinetExpense): RedirectResponse
    {
        $this->authorizeStaff();
        $cabinetExpense->delete();
        return redirect()->route('cabinet-expenses.index')->with('success', 'Enregistrement supprimé.');
    }

    public function markPaid(CabinetExpense $cabinetExpense): RedirectResponse
    {
        $this->authorizeStaff();
        $cabinetExpense->update(['paid_at' => $cabinetExpense->paid_at ? null : now()]);
        $label = $cabinetExpense->fresh()->paid_at ? 'Payée' : 'Non payée';
        return back()->with('success', "Charge marquée : {$label}.");
    }

    private function authorizeStaff(): void
    {
        if (! auth()->user()?->hasRole('staff') && ! can_access_dossier()) {
            abort(403, 'Réservé aux assistantes et à l\'équipe médicale.');
        }
    }
}
