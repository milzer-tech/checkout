<?php

return [
    'email' => [
        'from_name' => env('CUBA_TRAVEL_EMAIL_FROM_NAME', 'Checkout application'),
        'from' => env('CUBA_TRAVEL_FROM_EMAIL', 'no-reply@test.com'),
        'to' => json_decode(env('CUBA_TRAVEL_TO_EMAILS', '["me@test.com", "they@test.com"]')),
    ],

    'active' => (bool) env('CUBA_TRAVEL_ACTIVE', false),

    'reasons' => json_decode(env('CUBA_TRAVEL_REASONS', '["Family Visits","Official Business of the U.S. Government, Foreign Governments, or Certain Intergovernmental Organizations","Journalistic Activity","Professional Research or Professional Meetings","Educational Activities (including study abroad programs)","Religious Activities","public Performances, Clinics, Workshops, Athletic or Other Competitions, and Exhibitions","Support for the Cuban People","Humanitarian Projects","Activities of private Foundations or Research or Educational Institutes","Exportation, Importation, or Transmission of Information or Informational Materials","Certain Authorized Export Transactions(relating to authorized exports under U . S . law)"]')),
];
