<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $quote->number }}</title>
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

    @php $d = $quote->custom_data ?? []; @endphp
    <div class="doc-type">DEVIS</div>
    <div class="doc-number">N° {{ $quote->number }}</div>

    <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <div class="block-label">Nom et prénom</div>
                <div class="block-value"><strong>{{ $quote->patient->fullName() }}</strong></div>
                @if(!empty($d['diagnostic']))
                    <div class="block-label">Diagnostic</div>
                    <div class="block-value">{{ $d['diagnostic'] }}</div>
                @endif
            </td>
            <td style="width:40%; vertical-align:top;">
                <div class="block-label">Date du devis</div>
                <div class="block-value">{{ $quote->quote_date->format('d/m/Y') }}</div>
                @if($quote->valid_until)
                    <div class="block-label">Valide jusqu'au</div>
                    <div class="block-value">{{ $quote->valid_until->format('d/m/Y') }}</div>
                @endif
                @if(!empty($d['treating_doctor']))
                    <div class="block-label">Médecin traitant</div>
                    <div class="block-value">{{ $d['treating_doctor'] }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Rubrique</th>
                <th>Détail</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($d['sessions_count']))
                <tr>
                    <td>Nombre de séances</td>
                    <td>{{ $d['sessions_count'] }} séances</td>
                </tr>
            @endif
            @if(!empty($d['sessions_rhythm']))
                <tr>
                    <td>Rythme des séances</td>
                    <td>{{ $d['sessions_rhythm'] }}</td>
                </tr>
            @endif
            @if(!empty($d['treatment_start']) || !empty($d['treatment_end']))
                <tr>
                    <td>Durée des séances</td>
                    <td>
                        @if(!empty($d['treatment_start']))
                            Du {{ \Carbon\Carbon::parse($d['treatment_start'])->format('d/m/Y') }}
                        @endif
                        @if(!empty($d['treatment_end']))
                            au {{ \Carbon\Carbon::parse($d['treatment_end'])->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
            @endif
            <tr>
                <td>Prix unitaire</td>
                <td>
                    @if(!empty($d['unit_price']))
                        {{ format_dh($d['unit_price']) }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr class="total-row">
                <td class="total-label">Total TTC</td>
                <td class="amount">{{ format_dh($quote->amount) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
