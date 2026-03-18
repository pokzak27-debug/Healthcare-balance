<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport mensuel détaillé {{ $month }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 20px; margin-bottom: 4px; color: #1e293b; }
        h2 { font-size: 14px; margin: 20px 0 10px; color: #334155; border-bottom: 2px solid #3b82f6; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .summary-table td:first-child { width: 35%; font-weight: bold; background: #f8fafc; }
        .total-row { font-weight: bold; background: #eff6ff !important; }
        .label { font-weight: bold; }
        .text-right { text-align: right; }
        .header-info { color: #64748b; font-size: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 24px; }
    </style>
</head>
<body>
    <h1>Rapport mensuel d'activité — {{ \Carbon\Carbon::parse($start)->locale('fr')->translatedFormat('F Y') }}</h1>
    <p class="header-info">Période : {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }} | Généré le {{ now()->format('d/m/Y à H:i') }} — Healthcare Balance</p>

    <div class="section">
        <h2>Synthèse globale</h2>
        <table class="summary-table">
            <tr><td>Chiffre d'affaires (DH)</td><td class="text-right">{{ format_dh($ca) }}</td></tr>
            <tr><td>Factures émises</td><td class="text-right">{{ $invoicesCount }}</td></tr>
            <tr><td>Rendez-vous prévus</td><td class="text-right">{{ $appointmentsTotal }}</td></tr>
            <tr><td>Rendez-vous honorés</td><td class="text-right">{{ $appointmentsCompleted }}</td></tr>
            <tr><td>Rendez-vous annulés</td><td class="text-right">{{ $statsByStatus['cancelled'] ?? 0 }}</td></tr>
            <tr><td>Patients absents</td><td class="text-right">{{ $statsByStatus['no_show'] ?? 0 }}</td></tr>
            @if($appointmentsTotal > 0)
            <tr><td>Taux d'honorabilité</td><td class="text-right">{{ number_format(($appointmentsCompleted / $appointmentsTotal) * 100, 1) }} %</td></tr>
            @endif
        </table>
    </div>

    <div class="section">
        <h2>Répartition des rendez-vous par statut</h2>
        <table>
            <thead><tr><th>Statut</th><th class="text-right">Nb RDV</th></tr></thead>
            <tbody>
                @php $labels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent']; @endphp
                @foreach($labels as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-right">{{ $statsByStatus[$key] ?? 0 }}</td>
                    </tr>
                @endforeach
                <tr class="total-row"><td>TOTAL</td><td class="text-right">{{ $appointmentsTotal }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Chiffre d'affaires par prestation</h2>
        <table>
            <thead><tr><th>Prestation</th><th class="text-right">Nb factures</th><th class="text-right">Montant (DH)</th></tr></thead>
            <tbody>
                @foreach($caByPrestation as $row)
                <tr>
                    <td>{{ $row['prestation'] }}</td>
                    <td class="text-right">{{ $row['count'] }}</td>
                    <td class="text-right">{{ format_dh($row['total']) }}</td>
                </tr>
                @endforeach
                <tr class="total-row"><td>TOTAL</td><td class="text-right">{{ $invoicesCount }}</td><td class="text-right">{{ format_dh($ca) }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Activité par salle</h2>
        <table>
            <thead><tr><th>Salle</th><th class="text-right">Rendez-vous</th></tr></thead>
            <tbody>
                @forelse($byRoom as $row)
                <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                @empty
                <tr><td colspan="2">Aucun RDV avec salle sur cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Activité par prestation</h2>
        <table>
            <thead><tr><th>Prestation</th><th class="text-right">Rendez-vous</th></tr></thead>
            <tbody>
                @forelse($byPrestation as $row)
                <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                @empty
                <tr><td colspan="2">Aucun RDV avec prestation sur cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Répartition des rendez-vous par pôle</h2>
        <table>
            <thead><tr><th>Pôle</th><th class="text-right">Rendez-vous</th></tr></thead>
            <tbody>
                @forelse($byPole as $row)
                <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                @empty
                <tr><td colspan="2">Aucun RDV sur cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!empty($byPractitioner))
    <div class="section">
        <h2>Activité par praticien</h2>
        <table>
            <thead><tr><th>Praticien</th><th class="text-right">RDV honorés</th><th class="text-right">CA (DH)</th></tr></thead>
            <tbody>
                @foreach($byPractitioner as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right">{{ $row['completed'] }}</td>
                    <td class="text-right">{{ format_dh($row['ca'] ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <p style="margin-top: 30px; font-size: 9px; color: #94a3b8;">Document confidentiel — Healthcare Balance</p>
</body>
</html>
