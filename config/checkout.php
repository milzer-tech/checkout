<?php

use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Insurances\Providers\HanseMerkur\HanseMerkurInsurance;
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
            'active' => (bool) env('CHECKOUT_COMPUTOP_ACTIVE', false),
            'test_mode' => (bool) env('CHECKOUT_COMPUTOP_TEST_MODE', false),
            'name' => env('CHECKOUT_COMPUTOP_NAME', 'Computop'),
            'base_url' => env('CHECKOUT_COMPUTOP_BASE_URL', 'https://www.computop-paygate.com/api/v1'),
            'username' => env('CHECKOUT_COMPUTOP_USERNAME', 'must_be_set_in_env'),
            'password' => env('CHECKOUT_COMPUTOP_PASSWORD', 'must_be_set_in_env'),
        ],
    ],

    'insurance' => [
        'vertical' => [
            'active' => (bool) env('CHECKOUT_VERTICAL_INSURACNE_ACTIVE', false),
            'name' => env('CHECKOUT_VERTICAL_INSURACNE_NAME', 'Vertical Insurance'),
            'connected_account_id' => env('CHECKOUT_VERTICAL_INSURANCE_CONNECTED_ACCOUNT_ID', 'must_be_set_in_env'),
            'username' => env('CHECKOUT_VERTICAL_INSURANCE_USERNAME', 'must_be_set_in_env'),
            'password' => env('CHECKOUT_VERTICAL_INSURANCE_PASSWORD', 'must_be_set_in_env'),
        ],

        'hanse_merkur' => [
            'active' => (bool) env('CHECKOUT_HANSE_MERKUR_INSURANCE_ACTIVE', false),
            'name' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_NAME', 'Hanse Merkur'),
            'offers_base_url' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_OFFERS_BASE_URL', 'https://api-fbt.hmrv.de/rest'),
            'payment_base_url' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_PAYMENT_BASE_URL', 'https://payment-test.hmrv.de/rest'),
            'username' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_USERNAME', 'must_be_set_in_env'),
            'password' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_PASSWORD', 'must_be_set_in_env'),
            'api_key' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_API_KEY', 'must_be_set_in_env'),
            'requester_id' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_REQUESTER_ID', 'must_be_set_in_env'),
            'partner_id' => env('CHECKOUT_HANSE_MERKUR_INSURANCE_PARTNER_ID', 'must_be_set_in_env'),
        ],

        /**
         * ERGO / ERV eSoap (SOAP 1.1). WSDL ships with the package next to ErgoConnector.
         *
         * Defaults target the public ERV test gateway; override via env in production.
         */
        'ergo' => [
            'active' => (bool) env('CHECKOUT_ERGO_INSURANCE_ACTIVE', false),
            'name' => env('CHECKOUT_ERGO_INSURANCE_NAME', 'ERV Reiseversicherung'),
            'base_url' => env('CHECKOUT_ERGO_INSURANCE_BASE_URL', 'https://egate2.erv.de/esc201909/ESCConnector'),
            'crs' => env('CHECKOUT_ERGO_INSURANCE_CRS', 'ESC_DE'),
            'crs_agency' => env('CHECKOUT_ERGO_INSURANCE_CRS_AGENCY', '033315000000'),
            'initiator' => env('CHECKOUT_ERGO_INSURANCE_INITIATOR', 'customer'),
            'agent' => env('CHECKOUT_ERGO_INSURANCE_AGENT', 'customer'),
            'locale_country' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_COUNTRY', 'DE'),
            'locale_language' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_LANGUAGE', 'de'),
            'locale_currency' => env('CHECKOUT_ERGO_INSURANCE_LOCALE_CURRENCY', 'EUR'),
            'list_type' => env('CHECKOUT_ERGO_INSURANCE_LIST_TYPE', 'DE_STANDARD'),
            'auto_quote' => (bool) env('CHECKOUT_ERGO_INSURANCE_AUTO_QUOTE', true),
            'echo_token' => env('CHECKOUT_ERGO_INSURANCE_ECHO_TOKEN'),
            'transaction_context' => env('CHECKOUT_ERGO_INSURANCE_TRANSACTION_CONTEXT'),
        ],
    ],

    'payment' => [
        OppwaWidgetGateway::class,
        InvoiceGateway::class,
        StripeGateway::class,
        ComputopGateway::class,
    ],

    'insurance_provider' => [
        HanseMerkurInsurance::class,
        ErgoInsurance::class,
    ],

    'term_limit' => (int) env('CHECKOUT_TERM_LIMIT', 600),

    'fake_calls' => env('CHECKOUT_FAKE_NEZASA_CALLS', false),
];
