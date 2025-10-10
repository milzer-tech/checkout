<?php

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaStatusRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('sends OppwaStatusRequest with correct method, endpoint, headers and query; validates success detection', function (): void {
    Config::set('checkout.integrations.oppwa.base_url', 'https://oppwa.example.test');
    Config::set('checkout.integrations.oppwa.entity_id', 'entity-321');
    Config::set('checkout.integrations.oppwa.token', 'secret-token-xyz');
    Config::set('checkout.integrations.oppwa.successful_result_code', '000.100.110'); // matches fixture

    $resourcePath = 'v1/checkouts/8ac7a4a198c97c2b0198cc4676cd7058/payment';

    $mockClient = new MockClient([
        OppwaStatusRequest::class => MockResponse::fixture('oppwa_status_response'),
    ]);

    $connector = new OppwaConnector;
    $connector->withMockClient($mockClient);
    $response = $connector->checkout()->status($resourcePath);

    $mockClient->assertSent(function (OppwaStatusRequest $pending) use ($resourcePath): bool {
        expect($pending->getMethod()->value)->toBe('GET');
        expect($pending->resolveEndpoint())->toContain($resourcePath);

        return true;
    });

    $data = $response->array();

    expect($data)
        ->toBeArray()
        ->toHaveKeys(['id', 'ndc', 'result', 'currency', 'timestamp'])
        ->and($data['result'])
        ->toBeArray()
        ->toHaveKeys(['code', 'description'])
        ->and($data['result']['code'])
        ->toBe('000.100.110');

    $request = new OppwaStatusRequest($resourcePath);
    expect($request->hasRequestFailed($response))->toBeFalse();
});

it('detects failure when result code does not match configured success code', function (): void {
    Config::set('checkout.payment.widget.oppwa.base_url', 'https://oppwa.example.test');
    Config::set('checkout.payment.widget.oppwa.entity_id', 'entity-321');
    Config::set('checkout.payment.widget.oppwa.token', 'secret-token-xyz');
    Config::set('checkout.payment.widget.oppwa.successful_result_code', '999.999.999'); // mismatch on purpose

    $resourcePath = 'v1/checkouts/8ac7a4a198c97c2b0198cc4676cd7058/payment';

    $mockClient = new MockClient([
        OppwaStatusRequest::class => MockResponse::fixture('oppwa_status_response'),
    ]);

    $connector = new OppwaConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->checkout()->status($resourcePath);

    $request = new OppwaStatusRequest($resourcePath);

    expect($request->hasRequestFailed($response))->toBeTrue();
});

it('returns failure on invalid response structure (missing result code)', function (): void {
    Config::set('checkout.payment.widget.oppwa.base_url', 'https://oppwa.example.test');
    Config::set('checkout.payment.widget.oppwa.entity_id', 'entity-321');
    Config::set('checkout.payment.widget.oppwa.token', 'secret-token-xyz');
    Config::set('checkout.payment.widget.oppwa.successful_result_code', '000.100.110');

    $resourcePath = 'v1/checkouts/any/payment';

    $mockClient = new MockClient([
        OppwaStatusRequest::class => MockResponse::make('{"foo":"bar"}', 200),
    ]);

    $connector = new OppwaConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->checkout()->status($resourcePath);

    $request = new OppwaStatusRequest($resourcePath);

    expect($request->hasRequestFailed($response))->toBeTrue();
});
