<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport mensuel {{ $month }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .label { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <h1>Rapport mensuel — {{ \Carbon\Carbon::parse($start)->locale('fr')->translatedFormat('F Y') }}</h1>
    <p><strong>Période :</strong> {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>

    <table>
        <tr>
            <td class="label">Chiffre d'affaires (DH)</td>
            <td>{{ format_dh($ca) }}</td>
        </tr>
        <tr>
            <td class="label">Factures émises</td>
            <td>{{ $invoicesCount }}</td>
        </tr>
        <tr>
            <td class="label">Rendez-vous total</td>
            <td>{{ $appointmentsTotal }}</td>
        </tr>
        <tr>
            <td class="label">Rendez-vous honorés</td>
            <td>{{ $appointmentsCompleted }}</td>
        </tr>
    </table>

    <p><small>Généré le {{ now()->format('d/m/Y H:i') }} — Healthcare Balance</small></p>
</body>
</html>
