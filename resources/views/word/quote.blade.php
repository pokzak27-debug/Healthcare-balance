@php $cabinet = config('cabinet'); @endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="utf-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Healthcare Balance">
    <title>Devis {{ $quote->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .header { border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 20px; }
        .doc-type { font-size: 18pt; font-weight: bold; color: #1e3a5f; margin-bottom: 16px; }
        .block-label { font-size: 9pt; color: #6b7280; margin-bottom: 2px; }
        table.data { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 8px 10px; }
        table.data th { background: #f3f4f6; }
        .amount { text-align: right; }
        .total-row { font-weight: bold; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="header">
        <strong style="font-size: 16pt; color: #1e3a5f;">{{ $cabinet['name'] ?? 'Healthcare Balance' }}</strong><br>
        <span style="font-size: 10pt; color: #374151;">{{ $cabinet['tagline'] ?? 'DO.MK. RHAK Fatima-Ezzahra' }}</span><br><br>
        <span style="font-size: 9pt; color: #6b7280;">{{ $cabinet['address'] ?? '' }}</span><br>
        Tél : {{ $cabinet['phone'] ?? '' }} @if(!empty($cabinet['phone_secondary'])) | {{ $cabinet['phone_secondary'] }} @endif<br>
        {{ $cabinet['email'] ?? '' }}
    </div>

    <div class="doc-type">DEVIS</div>
    <p><strong>N° {{ $quote->number }}</strong></p>
    <p><strong>Client :</strong> {{ $quote->patient->fullName() }}</p>
    <p><strong>Date :</strong> {{ $quote->quote_date->format('d/m/Y') }} @if($quote->valid_until) · <strong>Valide jusqu'au :</strong> {{ $quote->valid_until->format('d/m/Y') }} @endif</p>

    <table class="data">
        <tr><th>Désignation</th><th class="amount">Montant (DH)</th></tr>
        <tr><td>{{ $quote->description ?: 'Consultation / soins' }}</td><td class="amount">{{ format_dh($quote->amount) }}</td></tr>
        <tr class="total-row"><td>Total TTC</td><td class="amount">{{ format_dh($quote->amount) }}</td></tr>
    </table>
    @if($quote->notes)<p style="font-size: 9pt; color: #6b7280;">{{ $quote->notes }}</p>@endif
</body>
</html>
