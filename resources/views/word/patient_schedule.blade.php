@php $cabinet = config('cabinet'); @endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="utf-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Healthcare Balance">
    <title>Calendrier patient - {{ $patient->fullName() }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .doc-header { text-align: center; margin-bottom: 18px; }
        .title-main { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }
        .title-sub { font-size: 10pt; text-transform: uppercase; letter-spacing: 0.08em; }
        .contact { font-size: 9pt; margin-top: 6px; line-height: 1.4; }
        .doc-title { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .doc-subtitle { font-size: 9pt; color: #4b5563; margin-bottom: 12px; }
        .line-block { font-size: 11pt; margin-bottom: 6px; }
        .line-label { display: inline-block; min-width: 120px; }
        .line-dots { display: inline-block; min-width: 220px; border-bottom: 1px dotted #9ca3af; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 8px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 6px 8px; }
        table.data th { background: #f3f4f6; font-size: 9pt; }
        .note { font-size: 8.5pt; color: #4b5563; margin-top: 10px; }
        .signatures { margin-top: 24px; font-size: 10.5pt; width: 100%; }
        .sign-col { width: 50%; vertical-align: top; }
        .sign-label { margin-bottom: 24px; }
        .footnote { margin-top: 16px; font-size: 8.5pt; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div class="title-main">HEALTHCARE BALANCE</div>
        <div class="title-sub">DO.MK. RHAK FATIMA-EZZAHRA · Kinésithérapie • Ostéopathie • Esthétique médicale</div>
        <div class="contact">
            06 33 44 00 49 | fatie.rhak@gmail.com<br>
            11 Rue Akid Abdellah Ben Mamous, Lot Bennis, Quartier Azib Derai, Safi
        </div>
    </div>

    <div class="doc-title">CALENDRIER DES SÉANCES PATIENT</div>
    <div class="doc-subtitle">Document simplifié remis au patient</div>

    <div class="line-block">
        <span class="line-label">Nom du patient</span>
        <span class="line-dots">{{ $patient->fullName() }}</span>
        <span class="line-label" style="margin-left: 16px;">Diagnostic</span>
        <span class="line-dots"></span>
    </div>
    <div class="line-block">
        <span class="line-label">Période</span>
        <span class="line-dots"></span>
        <span class="line-label" style="margin-left: 16px;">Nombre de séances</span>
        <span class="line-dots">{{ $entries->count() ?: '' }}</span>
    </div>

    <div class="line-block" style="margin-top: 10px; font-size: 9pt;">
        Séances prévues — Les colonnes ci-dessous affichent uniquement la date, l’heure et le pôle.
    </div>

    <table class="data">
        <tr>
            <th style="width: 8%;">N°</th>
            <th style="width: 24%;">Date</th>
            <th style="width: 24%;">Heure</th>
            <th>Pôle</th>
        </tr>
        @php $maxRows = max($entries->count(), 12); @endphp
        @for($i = 0; $i < $maxRows; $i++)
            @php $entry = $entries->get($i); @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $entry?->session_date?->format('d/m/Y') }}</td>
                <td>{{ $entry?->session_time }}</td>
                <td>{{ $entry?->pole }}</td>
            </tr>
        @endfor
    </table>

    <div class="note">
        Note : ce calendrier est fourni au patient comme document de suivi thérapeutique. Il peut être mis à jour sans modifier le calendrier interne du cabinet.
    </div>

    <table class="signatures">
        <tr>
            <td class="sign-col" style="text-align:right;">
                <div class="sign-label">Cachet et signature du cabinet</div>
                <div>__________________________</div>
            </td>
        </tr>
    </table>

    <div class="footnote">
        Healthcare Balance - document patient
    </div>
</body>
</html>

