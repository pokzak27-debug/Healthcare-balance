<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .total { font-size: 16px; font-weight: bold; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <h1>Facture {{ $invoice->number }}</h1>
    <p><strong>Date :</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
    <p><strong>Patient :</strong> {{ $invoice->patient->fullName() }}</p>
    @if($invoice->description)
        <p>{{ $invoice->description }}</p>
    @endif
    <table>
        <tr>
            <th>Description</th>
            <th class="amount">Montant (DH)</th>
        </tr>
        <tr>
            <td>{{ $invoice->description ?: 'Consultation / soins' }}</td>
            <td class="amount">{{ format_dh($invoice->amount) }}</td>
        </tr>
    </table>
    <p class="total">Total : {{ format_dh($invoice->amount) }}</p>
    @if($invoice->notes)
        <p><small>{{ $invoice->notes }}</small></p>
    @endif
</body>
</html>
