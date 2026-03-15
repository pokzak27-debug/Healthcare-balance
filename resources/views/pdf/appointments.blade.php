<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rendez-vous - Healthcare Balance</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 16pt; margin-bottom: 16px; color: #4f46e5; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 600; }
        tr:nth-child(even) { background: #f9fafb; }
        .meta { font-size: 9pt; color: #6b7280; margin-bottom: 12px; }
        .footer { margin-top: 24px; font-size: 8pt; color: #9ca3af; }
    </style>
</head>
<body>
    <h1>Rendez-vous – Healthcare Balance</h1>
    <p class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Durée</th>
                <th>Patient</th>
                <th>Pôle</th>
                <th>Praticien</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @php $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent']; @endphp
            @foreach($appointments as $apt)
                <tr>
                    <td>{{ $apt->appointment_date->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}</td>
                    <td>{{ $apt->duration_minutes ?? 30 }} min</td>
                    <td>{{ $apt->patient?->fullName() ?? '—' }}</td>
                    <td>{{ $apt->pole?->name ?? '—' }}</td>
                    <td>{{ $apt->user?->name ?? '—' }}</td>
                    <td>{{ $statusLabels[$apt->status] ?? $apt->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ count($appointments) }} rendez-vous – Healthcare Balance</p>
</body>
</html>
