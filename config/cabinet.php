<?php

return [
    'name' => env('CABINET_NAME', 'Healthcare Balance'),
    'tagline' => env('CABINET_TAGLINE', 'DO.MK. RHAK Fatima-Ezzahra'),
    'address' => env('CABINET_ADDRESS', '11 Rue Akid Abdellah Ben Mamous, Lot Bennis, Azib Derai, Safi'),
    'phone' => env('CABINET_PHONE', '06 33 44 00 49'),
    'phone_secondary' => env('CABINET_PHONE_SECONDARY', '05 24 62 57 63'),
    'email' => env('CABINET_EMAIL', 'Healthcarebalance4@gmail.com'),
    // Chemin absolu (ou dans /public via public_path) vers le logo si disponible.
    // Exemple: CABINET_LOGO_PATH=/Users/.../public/images/logo.png
    'logo_path' => env('CABINET_LOGO_PATH'),
];
