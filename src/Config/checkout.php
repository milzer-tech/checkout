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
];
