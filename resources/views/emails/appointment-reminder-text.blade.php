Rappel de rendez-vous - Healthcare Balance

Bonjour {{ $appointment->patient->first_name }},

Ceci est un rappel pour votre rendez-vous prévu demain :

Date : {{ $appointment->appointment_date->translatedFormat('l d F Y') }}
Heure : {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} – {{ $appointment->endTime() }} ({{ $appointment->duration_minutes ?? 30 }} min)
@if($appointment->pole)
Pôle : {{ $appointment->pole->name }}
@endif
@if($appointment->user)
Avec : {{ $appointment->user->name }}
@endif
@if($appointment->reason)
Motif : {{ $appointment->reason }}
@endif

En cas d'empêchement, merci de contacter le cabinet pour annuler ou reprogrammer.

---
Cet email a été envoyé automatiquement par Healthcare Balance.
