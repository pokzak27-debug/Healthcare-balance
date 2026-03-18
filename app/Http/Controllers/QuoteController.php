<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quote::with(['patient', 'appointment'])->orderBy('quote_date', 'desc');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($patientId = $request->get('patient_id')) {
            $query->where('patient_id', $patientId);
        }
        $quotes = $query->paginate(15)->withQueryString();

        return view('quotes.index', compact('quotes'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        $appointment = $request->get('appointment_id') ? Appointment::find($request->get('appointment_id')) : null;

        return view('quotes.create', compact('patients', 'appointment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,sent,accepted,rejected'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'diagnostic' => ['nullable', 'string', 'max:255'],
            'treating_doctor' => ['nullable', 'string', 'max:255'],
            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'sessions_rhythm' => ['nullable', 'string', 'max:255'],
            'treatment_start' => ['nullable', 'date'],
            'treatment_end' => ['nullable', 'date'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $validated['number'] = Quote::generateNumber();
        $custom = [
            'diagnostic' => $validated['diagnostic'] ?? null,
            'treating_doctor' => $validated['treating_doctor'] ?? null,
            'sessions_count' => $validated['sessions_count'] ?? null,
            'sessions_rhythm' => $validated['sessions_rhythm'] ?? null,
            'treatment_start' => $validated['treatment_start'] ?? null,
            'treatment_end' => $validated['treatment_end'] ?? null,
            'unit_price' => $validated['unit_price'] ?? null,
        ];
        unset(
            $validated['diagnostic'],
            $validated['treating_doctor'],
            $validated['sessions_count'],
            $validated['sessions_rhythm'],
            $validated['treatment_start'],
            $validated['treatment_end'],
            $validated['unit_price']
        );
        $validated['custom_data'] = $custom;

        Quote::create($validated);

        return redirect()->route('quotes.index')->with('success', 'Devis créé.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['patient', 'appointment', 'invoice']);

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load(['patient', 'appointment']);
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();

        return view('quotes.edit', compact('quote', 'patients'));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,sent,accepted,rejected'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'diagnostic' => ['nullable', 'string', 'max:255'],
            'treating_doctor' => ['nullable', 'string', 'max:255'],
            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'sessions_rhythm' => ['nullable', 'string', 'max:255'],
            'treatment_start' => ['nullable', 'date'],
            'treatment_end' => ['nullable', 'date'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $custom = array_merge($quote->custom_data ?? [], [
            'diagnostic' => $validated['diagnostic'] ?? null,
            'treating_doctor' => $validated['treating_doctor'] ?? null,
            'sessions_count' => $validated['sessions_count'] ?? null,
            'sessions_rhythm' => $validated['sessions_rhythm'] ?? null,
            'treatment_start' => $validated['treatment_start'] ?? null,
            'treatment_end' => $validated['treatment_end'] ?? null,
            'unit_price' => $validated['unit_price'] ?? null,
        ]);
        unset(
            $validated['diagnostic'],
            $validated['treating_doctor'],
            $validated['sessions_count'],
            $validated['sessions_rhythm'],
            $validated['treatment_start'],
            $validated['treatment_end'],
            $validated['unit_price']
        );
        $validated['custom_data'] = $custom;

        $quote->update($validated);

        return redirect()->route('quotes.index')->with('success', 'Devis mis à jour.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function accept(Quote $quote): RedirectResponse
    {
        if (! $quote->canBeAccepted()) {
            return redirect()->route('quotes.show', $quote)->with('error', 'Ce devis ne peut plus être accepté.');
        }
        $quote->update(['status' => 'accepted']);

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis accepté.');
    }

    public function reject(Quote $quote): RedirectResponse
    {
        if (! $quote->canBeAccepted()) {
            return redirect()->route('quotes.show', $quote)->with('error', 'Ce devis ne peut plus être refusé.');
        }
        $quote->update(['status' => 'rejected']);

        return redirect()->route('quotes.index')->with('success', 'Devis refusé.');
    }

    public function convertToInvoice(Quote $quote): RedirectResponse
    {
        if (! $quote->canBeConvertedToInvoice()) {
            return redirect()->route('quotes.show', $quote)->with('error', 'Ce devis ne peut pas être converti en facture.');
        }

        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $quote->patient_id,
            'appointment_id' => $quote->appointment_id,
            'amount' => $quote->amount,
            'status' => 'draft',
            'invoice_date' => now(),
            'description' => $quote->description,
            'notes' => $quote->notes,
        ]);

        $quote->update(['invoice_id' => $invoice->id]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture créée à partir du devis.');
    }

    public function duplicate(Quote $quote): RedirectResponse
    {
        $newQuote = $quote->replicate();
        $newQuote->number = Quote::generateNumber();
        $newQuote->status = 'draft';
        $newQuote->invoice_id = null;
        $newQuote->save();

        return redirect()->route('quotes.edit', $newQuote)->with('success', 'Devis dupliqué. Vous pouvez le modifier.');
    }

    public function exportPdf(Quote $quote)
    {
        $quote->load(['patient', 'appointment']);

        $pdf = Pdf::loadView('pdf.quote', compact('quote'));

        return $pdf->download('devis-' . $quote->number . '.pdf');
    }

    public function exportWord(Quote $quote)
    {
        $quote->load(['patient', 'appointment']);
        $html = view('word.quote', compact('quote'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="devis-' . $quote->number . '.doc"',
        ]);
    }
}
