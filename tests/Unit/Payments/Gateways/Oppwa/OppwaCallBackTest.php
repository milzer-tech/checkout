<?php

use Illuminate\Http\Request;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaStatusRequest;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaCallBack;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('checks payment status and returns Succeeded with persistent data on successful Oppwa status', function () {
    config()->set('checkout.payment.widget.oppwa.successful_result_code', '000.100.110');

    MockClient::global([
        OppwaStatusRequest::class => MockResponse::fixture('oppwa_status_response'),
    ]);

    $callback = new OppwaCallBack;

    /** @var Request $request */
    $request = Request::create('/callback', 'GET', ['resourcePath' => '/v1/checkouts/abc123']);

    $result = $callback->check($request, ['any' => 'data']);

    expect($result)
        ->toBeInstanceOf(PaymentResult::class)
        ->and($result->gateway)->toBe(PaymentGatewayEnum::Oppwa)
        ->and($result->status)->toBe(PaymentStatusEnum::Succeeded)
        ->and($result->persistentData)
        ->toBeArray()
        ->not()->toBeEmpty();
});

it('returns Failed when Oppwa status indicates failure (non-matching result.code)', function () {
    config()->set('checkout.payment.widget.oppwa.successful_result_code', '999.999.999');

    MockClient::global([
        OppwaStatusRequest::class => MockResponse::fixture('oppwa_status_response'),
    ]);

    $callback = new OppwaCallBack;
    $request = Request::create('/callback', 'GET', ['resourcePath' => '/v1/checkouts/abc123']);

    $result = $callback->check($request, []);

    expect($result->status)->toBe(PaymentStatusEnum::Failed)
        ->and($result->persistentData)->toBeArray()->not()->toBeEmpty();
});

it('returns Failed when an exception occurs while checking status', function () {
    config()->set('checkout.payment.widget.oppwa.successful_result_code', '000.100.110');

    MockClient::global([
        OppwaStatusRequest::class => MockResponse::make('not-json', 500),
    ]);

    $callback = new OppwaCallBack;
    $request = Request::create('/callback', 'GET', ['resourcePath' => '/v1/checkouts/abc123']);

    $result = $callback->check($request, []);

    expect($result->status)->toBe(PaymentStatusEnum::Failed)
        ->and($result->persistentData)->toBe([]);
});

it('show returns the provided PaymentOutput without modification', function () {
    $callback = new OppwaCallBack;

    $result = new PaymentResult(PaymentGatewayEnum::Oppwa, PaymentStatusEnum::Succeeded, ['foo' => 'bar']);
    $output = new PaymentOutput(
        gateway: $result->gateway,
        isNezasaBookingSuccessful: true,
        bookingReference: 'itn_123',
        orderDate: null,
        data: ['baz' => 'qux']
    );

    $returned = $callback->show($result, $output);

    expect($returned)->toBe($output)
        ->and($returned->data)->toBe(['baz' => 'qux']);
});

it('returns the list of added params to be ignored for signature validation', function () {
    $callback = new OppwaCallBack;

    $request = Request::create('/callback', 'GET', [
        'resourcePath' => '/v1/checkouts/abc123',
        'id' => 'xyz',
        'irrelevant' => 'param',
    ]);

    $params = $callback->addedParamsToReturnedUrl($request);

    expect($params)->toBe(['resourcePath', 'id']);
});
