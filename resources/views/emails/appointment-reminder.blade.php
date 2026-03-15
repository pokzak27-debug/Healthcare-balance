<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel rendez-vous</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; color: #374151; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; }
        .detail { margin: 12px 0; }
        .label { font-weight: 600; color: #6b7280; font-size: 0.875rem; }
        .value { font-size: 1rem; }
        .footer { margin-top: 24px; font-size: 0.75rem; color: #9ca3af; }
        h1 { margin: 0; font-size: 1.25rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rappel de rendez-vous</h1>
        <p style="margin: 8px 0 0; opacity: 0.9;">Cabinet Healthcare Balance</p>
    </div>
    <div class="content">
        <p>Bonjour {{ $appointment->patient->first_name }},</p>
        <p>Ceci est un rappel pour votre rendez-vous prévu <strong>demain</strong> :</p>

        <div class="detail">
            <span class="label">Date :</span>
            <span class="value">{{ $appointment->appointment_date->translatedFormat('l d F Y') }}</span>
        </div>
        <div class="detail">
            <span class="label">Heure :</span>
            <span class="value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} – {{ $appointment->endTime() }} ({{ $appointment->duration_minutes ?? 30 }} min)</span>
        </div>
        @if($appointment->pole)
            <div class="detail">
                <span class="label">Pôle :</span>
                <span class="value">{{ $appointment->pole->name }}</span>
            </div>
        @endif
        @if($appointment->user)
            <div class="detail">
                <span class="label">Avec :</span>
                <span class="value">{{ $appointment->user->name }}</span>
            </div>
        @endif
        @if($appointment->reason)
            <div class="detail">
                <span class="label">Motif :</span>
                <span class="value">{{ $appointment->reason }}</span>
            </div>
        @endif

        <p>En cas d'empêchement, merci de contacter le cabinet pour annuler ou reprogrammer.</p>
    </div>
    <div class="footer">
        Cet email a été envoyé automatiquement par Healthcare Balance. Ne pas répondre directement à ce message.
    </div>
</body>
</html>
