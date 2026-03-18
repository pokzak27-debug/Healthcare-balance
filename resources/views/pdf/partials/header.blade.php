@php
    $cabinet = config('cabinet');
@endphp
<table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:24px; border-bottom:2px solid #1e3a5f;">
    <tr>
        <td style="vertical-align:top; width:60%;">
            <strong style="font-size:18px; color:#1e3a5f;">{{ $cabinet['name'] ?? 'Healthcare Balance' }}</strong><br>
            <span style="font-size:11px; color:#374151;">{{ $cabinet['tagline'] ?? 'DO.MK. RHAK Fatima-Ezzahra' }}</span><br><br>
            <span style="font-size:10px; color:#6b7280;">{{ $cabinet['address'] ?? '' }}</span><br>
            <span style="font-size:10px;">Tél : {{ $cabinet['phone'] ?? '' }} @if(!empty($cabinet['phone_secondary'])) | {{ $cabinet['phone_secondary'] }} @endif</span><br>
            <span style="font-size:10px;">{{ $cabinet['email'] ?? '' }}</span>
        </td>
        <td style="vertical-align:top; text-align:right; width:40%;">
            {{-- Logo ou texte côté droit laissé vide ou numéro doc --}}
        </td>
    </tr>
</table>
