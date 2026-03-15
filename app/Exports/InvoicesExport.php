<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected ?string $status = null;
    protected ?string $from = null;
    protected ?string $to = null;

    public function __construct(?string $status = null, ?string $from = null, ?string $to = null)
    {
        $this->status = $status;
        $this->from = $from;
        $this->to = $to;
    }

    public function query()
    {
        $query = Invoice::with(['patient', 'appointment.user'])->orderBy('invoice_date', 'desc');

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->from) {
            $query->whereDate('invoice_date', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('invoice_date', '<=', $this->to);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['N°', 'Date', 'Patient', 'Montant (DH)', 'Statut', 'Praticien'];
    }

    public function map($invoice): array
    {
        return [
            $invoice->number,
            $invoice->invoice_date->format('d/m/Y'),
            $invoice->patient?->fullName(),
            (float) $invoice->amount,
            Invoice::STATUSES[$invoice->status] ?? $invoice->status,
            $invoice->appointment?->user?->name ?? '—',
        ];
    }
}
