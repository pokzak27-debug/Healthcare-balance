<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Exports\InvoicesExport;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\Prestation;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['patient', 'appointment'])->orderBy('invoice_date', 'desc');

        if (auth()->user()?->hasRole('staff')) {
            $query->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year);
        } elseif ($month = $request->get('month')) {
            $parts = explode('-', $month);
            if (count($parts) === 2 && can_access_dossier()) {
                $query->whereYear('invoice_date', $parts[0])
                    ->whereMonth('invoice_date', (int) $parts[1]);
            }
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($patientId = $request->get('patient_id')) {
            $query->where('patient_id', $patientId);
        }
        $invoices = $query->paginate(15)->withQueryString();

        $archivedMonths = collect();
        if (can_access_dossier()) {
            $archivedMonths = Invoice::selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month')
                ->groupBy('year', 'month')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->limit(24)
                ->get()
                ->map(fn ($r) => sprintf('%04d-%02d', $r->year, $r->month));
        }

        return view('invoices.index', compact('invoices', 'archivedMonths'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        $appointment = $request->get('appointment_id') ? Appointment::find($request->get('appointment_id')) : null;
        $prestations = Prestation::active()->ordered()->get();
        $machines = Machine::active()->ordered()->get();

        return view('invoices.create', compact('patients', 'appointment', 'prestations', 'machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'prestation_ids' => ['nullable', 'array'],
            'prestation_ids.*' => ['integer', 'exists:prestations,id'],
            'machine_ids' => ['nullable', 'array'],
            'machine_ids.*' => ['integer', 'exists:machines,id'],
        ]);

        $prestationIds = array_values(array_map('intval', $validated['prestation_ids'] ?? []));
        $machineIds = array_values(array_map('intval', $validated['machine_ids'] ?? []));
        unset($validated['prestation_ids'], $validated['machine_ids']);

        $validated['invoice_date'] = $validated['invoice_date'] ?? now()->toDateString();
        $validated['status'] = 'paid';
        $validated['number'] = Invoice::generateNumber();
        $validated['custom_data'] = [
            'prestation_ids' => $prestationIds,
            'machine_ids' => $machineIds,
        ];
        $validated['description'] = $this->buildDescriptionFromIds($prestationIds, $machineIds, $validated['description'] ?? null);

        Invoice::create($validated);

        return redirect()->route('invoices.index')->with('success', 'Facture créée.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'appointment']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        if (! can_access_dossier()) {
            if ($invoice->isConfirmed()) {
                abort(403, 'Facture confirmée : seule modification possible par le docteur ou l\'administrateur.');
            }
        }
        $invoice->load(['patient', 'appointment']);
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        $prestations = Prestation::active()->ordered()->get();
        $machines = Machine::active()->ordered()->get();

        return view('invoices.edit', compact('invoice', 'patients', 'prestations', 'machines'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if (! can_access_dossier() && $invoice->isConfirmed()) {
            abort(403, 'Facture confirmée : seule modification possible par le docteur ou l\'administrateur.');
        }
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,sent,paid,cancelled'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'prestation_ids' => ['nullable', 'array'],
            'prestation_ids.*' => ['integer', 'exists:prestations,id'],
            'machine_ids' => ['nullable', 'array'],
            'machine_ids.*' => ['integer', 'exists:machines,id'],
        ]);

        $prestationIds = array_values(array_map('intval', $validated['prestation_ids'] ?? []));
        $machineIds = array_values(array_map('intval', $validated['machine_ids'] ?? []));
        unset($validated['prestation_ids'], $validated['machine_ids']);

        $validated['custom_data'] = array_merge($invoice->custom_data ?? [], [
            'prestation_ids' => $prestationIds,
            'machine_ids' => $machineIds,
        ]);
        $validated['description'] = $this->buildDescriptionFromIds($prestationIds, $machineIds, $validated['description'] ?? null);

        $invoice->update($validated);

        return redirect()->route('invoices.index')->with('success', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (! can_access_dossier()) {
            abort(403, 'Seul le docteur ou l\'administrateur peut supprimer une facture.');
        }
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Facture supprimée.');
    }

    public function confirm(Invoice $invoice): RedirectResponse
    {
        if (can_access_dossier()) {
            abort(403, 'Cette action est réservée aux assistantes.');
        }
        if ($invoice->isConfirmed()) {
            return back()->with('info', 'Facture déjà confirmée.');
        }
        $invoice->update(['confirmed_at' => now()]);

        return redirect()->route('invoices.index')->with('success', 'Facture confirmée. Vous ne pouvez plus la modifier ni la supprimer.');
    }

    public function exportPdf(Invoice $invoice)
    {
        $invoice->load(['patient', 'appointment']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

        return $pdf->download('facture-' . $invoice->number . '.pdf');
    }

    public function exportWord(Invoice $invoice)
    {
        $invoice->load(['patient', 'appointment']);
        $html = view('word.invoice', compact('invoice'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="facture-' . $invoice->number . '.doc"',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $status = $request->get('status');
        $from = $request->get('from');
        $to = $request->get('to');
        if (auth()->user()?->hasRole('staff') && ! $from && ! $to) {
            $from = now()->startOfMonth()->format('Y-m-d');
            $to = now()->format('Y-m-d');
        }

        return Excel::download(
            new InvoicesExport($status, $from, $to),
            'factures-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $query = Invoice::with(['patient', 'appointment.user'])->orderBy('invoice_date', 'desc');
        if (auth()->user()?->hasRole('staff')) {
            $query->whereMonth('invoice_date', now()->month)->whereYear('invoice_date', now()->year);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        $invoices = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="factures-' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($out, ['N°', 'Date', 'Patient', 'Montant (DH)', 'Statut', 'Praticien']);
            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv->number,
                    $inv->invoice_date->format('d/m/Y'),
                    $inv->patient?->fullName(),
                    $inv->amount,
                    Invoice::STATUSES[$inv->status] ?? $inv->status,
                    $inv->appointment?->user?->name ?? '—',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function caByPractitioner(Request $request)
    {
        if (! can_access_dossier()) {
            abort(403, 'Seul le docteur ou l\'administrateur peut consulter le chiffre d\'affaires.');
        }
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $invoices = Invoice::query()
            ->with(['appointment.prestation', 'appointment.pole'])
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$from, $to])
            ->get();

        $data = $invoices->groupBy(fn ($i) => $i->appointment?->prestation_id ?? 0)
            ->map(fn ($group, $prestationId) => [
                'prestation' => $prestationId
                    ? ($group->first()->appointment?->prestation?->name ?? $group->first()->appointment?->pole?->name ?? '—')
                    : 'Sans prestation',
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $totalInvoices = $invoices->count();
        $totalAmount = (float) $invoices->sum('amount');

        if ($request->wantsJson() || $request->has('format') && $request->get('format') === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="ca-' . $from . '-' . $to . '.csv"',
            ];
            return response()->stream(function () use ($data, $from, $to, $totalInvoices, $totalAmount) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($out, ['Période', $from . ' - ' . $to]);
                fputcsv($out, ['Prestation', 'Nb factures', 'Montant (DH)']);
                foreach ($data as $row) {
                    fputcsv($out, [$row['prestation'], $row['count'], $row['total']]);
                }
                fputcsv($out, ['TOTAL', $totalInvoices, $totalAmount]);
                fclose($out);
            }, 200, $headers);
        }

        return view('invoices.ca-by-practitioner', compact('data', 'from', 'to', 'totalInvoices', 'totalAmount'));
    }

    private function buildDescriptionFromIds(array $prestationIds, array $machineIds, ?string $fallbackText): string
    {
        $parts = [];
        if (! empty($prestationIds)) {
            $byId = Prestation::whereIn('id', $prestationIds)->get()->keyBy('id');
            $names = collect($prestationIds)->map(fn ($id) => $byId->get($id)?->name)->filter()->toArray();
            $parts[] = implode(', ', $names);
        }
        if (! empty($machineIds)) {
            $byId = Machine::whereIn('id', $machineIds)->get()->keyBy('id');
            $names = collect($machineIds)->map(fn ($id) => $byId->get($id)?->name)->filter()->toArray();
            $parts[] = implode(', ', $names);
        }
        if (! empty($parts)) {
            $desc = implode(' — ', $parts);
            if ($fallbackText !== null && $fallbackText !== '') {
                $desc .= ' — ' . $fallbackText;
            }
            return $desc;
        }

        return $fallbackText ?? '';
    }
}
