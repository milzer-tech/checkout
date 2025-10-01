<?php

return [

    /**
     * All the configuration options for the Nezasa API is defined here.
     */
    'nezasa' => [
        'base_url' => env('CHECKOUT_NEZASA_BASE_URL', 'https://api.tripbuilder.app'),
        'username' => env('CHECKOUT_NEZASA_USERNAME', 'must_be_set_in_env'),
        'password' => env('CHECKOUT_NEZASA_PASSWORD', 'must_be_set_in_env'),
    ],

    'payment' => [
        'widget' => [
            'oppwa' => [
                'active' => env('CHECKOUT_WIDGET_OPPWA_ACTIVE', false),
                'name' => env('CHECKOUT_WIDGET_OPPWA_NAME', 'oppwa'),
                'base_url' => env('CHECKOUT_WIDGET_OPPWA_BASE_URL', 'https://eu-test.oppwa.com'),
                'entity_id' => env('CHECKOUT_WIDGET_OPPWA_ENTITY_ID', 'must_be_set_in_env'),
                'token' => env('CHECKOUT_WIDGET_OPPWA_TOKEN', 'must_be_set_in_env'),
                'successful_result_code' => env('CHECKOUT_WIDGET_OPPWA_SUCCESSFUL_RESULT_CODE', '000.000.000'),
            ],
        ],
    ],
];
