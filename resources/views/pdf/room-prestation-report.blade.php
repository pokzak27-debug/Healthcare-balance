<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport salles et prestations {{ $month }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 20px; }
        h2 { font-size: 14px; margin-top: 24px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Rapport par salle et prestation — {{ \Carbon\Carbon::parse($start)->locale('fr')->translatedFormat('F Y') }}</h1>
    <p><strong>Période :</strong> {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>

    <h2>Par salle</h2>
    <table>
        <tr><th>Salle</th><th>Nombre de RDV</th></tr>
        @forelse($byRoom as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
        @empty
            <tr><td colspan="2">Aucun RDV avec salle sur cette période.</td></tr>
        @endforelse
    </table>

    <h2>Par prestation</h2>
    <table>
        <tr><th>Prestation</th><th>Nombre de RDV</th></tr>
        @forelse($byPrestation as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
        @empty
            <tr><td colspan="2">Aucun RDV avec prestation sur cette période.</td></tr>
        @endforelse
    </table>

    <p><small>Généré le {{ now()->format('d/m/Y H:i') }} — Healthcare Balance</small></p>
</body>
</html>
