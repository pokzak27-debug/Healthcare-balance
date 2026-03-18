<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .doc-type { font-size: 20px; font-weight: bold; color: #1e3a5f; margin-bottom: 4px; }
        .doc-number { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        .block-label { font-size: 9px; text-transform: uppercase; color: #6b7280; margin-bottom: 2px; }
        .block-value { font-size: 11px; margin-bottom: 10px; }
        table.data { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        table.data th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; color: #374151; }
        table.data .amount { text-align: right; }
        .total-row { font-weight: bold; font-size: 13px; background: #f9fafb; }
        .total-label { padding: 10px; }
        .notes { margin-top: 16px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="doc-type">FACTURE</div>
    <div class="doc-number">N° {{ $invoice->number }}</div>

    <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                <div class="block-label">Client</div>
                <div class="block-value"><strong>{{ $invoice->patient->fullName() }}</strong></div>
            </td>
            <td style="width:50%; vertical-align:top;">
                <div class="block-label">Date de facture</div>
                <div class="block-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    @php $descriptionLines = $invoice->getDescriptionLines(); @endphp
    <table class="data">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="amount" style="width:120px;">Montant (DH)</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($descriptionLines))
                @foreach($descriptionLines as $line)
                    <tr>
                        <td>{{ $line['label'] }}</td>
                        <td class="amount">—</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $invoice->description ?: 'Consultation / soins' }}</td>
                    <td class="amount">—</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="total-label">Total TTC</td>
                <td class="amount">{{ format_dh($invoice->amount) }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->notes)
        <div class="notes">{{ $invoice->notes }}</div>
    @endif
</body>
</html>
