<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $quote->number }}</title>
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
    <h1>Devis {{ $quote->number }}</h1>
    <p><strong>Date :</strong> {{ $quote->quote_date->format('d/m/Y') }}</p>
    @if($quote->valid_until)
        <p><strong>Valide jusqu'au :</strong> {{ $quote->valid_until->format('d/m/Y') }}</p>
    @endif
    <p><strong>Patient :</strong> {{ $quote->patient->fullName() }}</p>
    @if($quote->description)
        <p>{{ $quote->description }}</p>
    @endif
    <table>
        <tr>
            <th>Description</th>
            <th class="amount">Montant (DH)</th>
        </tr>
        <tr>
            <td>{{ $quote->description ?: 'Consultation / soins' }}</td>
            <td class="amount">{{ format_dh($quote->amount) }}</td>
        </tr>
    </table>
    <p class="total">Total : {{ format_dh($quote->amount) }}</p>
    @if($quote->notes)
        <p><small>{{ $quote->notes }}</small></p>
    @endif
</body>
</html>
