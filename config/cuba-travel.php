<?php

return [
    'email' => [
        'from_name' => env('CUBA_TRAVEL_EMAIL_FROM_NAME', 'Checkout application'),
        'from' => env('CUBA_TRAVEL_FROM_EMAIL', 'no-reply@test.com'),
        'to' => json_decode(env('CUBA_TRAVEL_TO_EMAILS', '["me@test.com", "they@test.com"]')),
    ],

    'active' => (bool) env('CUBA_TRAVEL_ACTIVE', false),

    'reasons' => json_decode(env('CUBA_TRAVEL_REASONS', '["holiday trip","beach vacation","sightseeing","visiting friends","attending a wedding","work conference","studying abroad","medical treatment"]')),
];
