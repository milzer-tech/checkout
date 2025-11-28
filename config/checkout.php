<?php

use Nezasa\Checkout\Payments\Gateways\Computop\ComputopGateway;
use Nezasa\Checkout\Payments\Gateways\Invoice\InvoiceGateway;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaWidgetGateway;
use Nezasa\Checkout\Payments\Gateways\Stripe\StripeGateway;

return [

    'distribution' => [
        'max_child_age' => env('MAC_CHILD_CHECKOUT_AGE', 17),
    ],

    /**
     * All the configuration options for the Nezasa API is defined here.
     */
    'nezasa' => [
        'base_url' => env('CHECKOUT_NEZASA_BASE_URL', 'https://api.tripbuilder.app'),
        'username' => env('CHECKOUT_NEZASA_USERNAME', 'must_be_set_in_env'),
        'password' => env('CHECKOUT_NEZASA_PASSWORD', 'must_be_set_in_env'),
    ],

    'integrations' => [
        'oppwa' => [
            'active' => (bool) env('CHECKOUT_WIDGET_OPPWA_ACTIVE', false),
            'name' => env('CHECKOUT_WIDGET_OPPWA_NAME', 'oppwa'),
            'base_url' => env('CHECKOUT_WIDGET_OPPWA_BASE_URL', 'https://eu-test.oppwa.com'),
            'entity_id' => env('CHECKOUT_WIDGET_OPPWA_ENTITY_ID', 'must_be_set_in_env'),
            'token' => env('CHECKOUT_WIDGET_OPPWA_TOKEN', 'must_be_set_in_env'),
            'successful_result_code' => env('CHECKOUT_WIDGET_OPPWA_SUCCESSFUL_RESULT_CODE', '000.000.000'),
        ],
        'invoice' => [
            'active' => (bool) env('CHECKOUT_WIDGET_INVOICE_ACTIVE', false),
            'name' => env('CHECKOUT_WIDGET_INVOICE_NAME', 'Invoice'),
        ],
        'stripe' => [
            'active' => (bool) env('CHECKOUT_STRIPE_ACTIVE', false),
            'name' => env('CHECKOUT_STRIPE_NAME', 'Stripe'),
            'secret_key' => env('CHECKOUT_STRIPE_SECRET_KEY', 'test'),
        ],
        'computop' => [
            'active' => (bool) env('CHECKOUT_COMPUTOP_ACTIVE', true),
            'name' => env('CHECKOUT_COMPUTOP_NAME', 'Computop'),
            'base_url' => env('CHECKOUT_COMPUTOP_BASE_URL', 'https://www.computop-paygate.com/api/v1'),
            'username' => env('CHECKOUT_COMPUTOP_USERNAME', 'must_be_set_in_env'),
            'password' => env('CHECKOUT_COMPUTOP_PASSWORD', 'must_be_set_in_env'),
        ],
    ],

    'payment' => [
        OppwaWidgetGateway::class,
        InvoiceGateway::class,
        StripeGateway::class,
        ComputopGateway::class,
    ],

    'fake_calls' => env('CHECKOUT_FAKE_NEZASA_CALLS', false),
];
