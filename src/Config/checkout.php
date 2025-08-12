<?php

return [

    /**
     * All the configuration options for the Nezasa API is defined here.
     */
    'nezasa' => [
        'base_url' => env('CHECKOUT_NEZASA_BASE_URL', 'https://api.tripbuilder.app'),
        'username' => env('CHECKOUT_NEZASA_USERNAME', 'your_username'),
        'password' => env('CHECKOUT_NEZASA_PASSWORD', 'your_password'),
    ],

    'payment' => [
        'oppwa' => [
            'name' => env('CHECKOUT_OPPWA_NAME', 'oppwa'),
            'base_url' => env('CHECKOUT_OPPWA_BASE_URL', 'https://eu-test.oppwa.com'),
            'entity_id' => env('CHECKOUT_OPPWA_ENTITY_ID', '8a8294184e736012014e78c4c4cb17dc'),
            'token' => env('CHECKOUT_OPPWA_TOKEN', 'OGE4Mjk0MTg0ZTczNjAxMjAxNGU3OGM0YzRlNDE3ZTB8NHRKQ21qMkJ0Mw=='),
        ],
    ],
];
