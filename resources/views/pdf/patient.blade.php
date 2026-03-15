<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche patient - {{ $patient->fullName() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 16pt; margin-bottom: 8px; color: #4f46e5; }
        h2 { font-size: 12pt; margin: 20px 0 8px; color: #374151; }
        .meta { font-size: 9pt; color: #6b7280; margin-bottom: 16px; }
        dl { margin: 0; }
        dt { font-weight: 600; color: #6b7280; font-size: 9pt; margin-top: 8px; }
        dd { margin: 2px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9pt; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .footer { margin-top: 24px; font-size: 8pt; color: #9ca3af; }
    </style>
</head>
<body>
    <h1>{{ $patient->fullName() }}</h1>
    <p class="meta">Fiche patient – Healthcare Balance – Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <h2>Informations personnelles</h2>
    <dl>
        <dt>Prénom</dt>
        <dd>{{ $patient->first_name }}</dd>
        <dt>Nom</dt>
        <dd>{{ $patient->last_name }}</dd>
        <dt>Date de naissance</dt>
        <dd>{{ $patient->birth_date?->format('d/m/Y') ?? '—' }}</dd>
        <dt>Sexe</dt>
        <dd>{{ match($patient->sexe ?? '') { 'M' => 'Homme', 'F' => 'Femme', 'autre' => 'Autre', default => '—' } }}</dd>
        <dt>Téléphone</dt>
        <dd>{{ $patient->phone ?? '—' }}</dd>
        <dt>Email</dt>
        <dd>{{ $patient->email ?? '—' }}</dd>
        <dt>Adresse</dt>
        <dd>{{ $patient->address ?? '—' }}</dd>
        @if($patient->notes)
        <dt>Notes</dt>
        <dd>{{ $patient->notes }}</dd>
        @endif
    </dl>

    <h2>Rendez-vous</h2>
    @if($appointments->isEmpty())
        <p>Aucun rendez-vous enregistré.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
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
                        <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }} ({{ $apt->duration_minutes ?? 30 }} min)</td>
                        <td>{{ $apt->pole?->name ?? '—' }}</td>
                        <td>{{ $apt->user?->name ?? '—' }}</td>
                        <td>{{ $statusLabels[$apt->status] ?? $apt->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Healthcare Balance – Cabinet de santé</p>
</body>
</html>
