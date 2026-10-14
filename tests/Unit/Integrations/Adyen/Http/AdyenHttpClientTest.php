<?php

declare(strict_types=1);

use Adyen\AdyenException;
use Adyen\Client;
use Adyen\ConnectionException;
use Adyen\Service\Checkout\PaymentsApi;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Nezasa\Checkout\Integrations\Adyen\Http\AdyenHttpClient;
use Nezasa\Checkout\Integrations\Adyen\Support\AdyenMerchantAccount;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaStatusRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    configureAdyen();
    Http::preventStrayRequests();
});

function adyenHttpClientService(): PaymentsApi
{
    return new PaymentsApi(AdyenMerchantAccount::forCurrency('USD')->client());
}

it('sends a JSON request with the API key, the idempotency key and the library headers', function (): void {
    Http::fake(['checkout-test.adyen.com/v71/sessions' => Http::response(['id' => 'CS-1'], 201)]);

    $result = (new AdyenHttpClient)->requestHttp(
        adyenHttpClientService(),
        'https://checkout-test.adyen.com/v71/sessions',
        ['merchantAccount' => 'MerchantUSD'],
        'post',
        ['idempotencyKey' => 'session-1', 'headers' => ['X-Custom' => 'custom']]
    );

    expect($result)->toBe(['id' => 'CS-1']);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://checkout-test.adyen.com/v71/sessions'
        && $request->data() === ['merchantAccount' => 'MerchantUSD']
        && $request->hasHeader('x-api-key', 'usd-api-key')
        && $request->hasHeader('Idempotency-Key', 'session-1')
        && $request->hasHeader('X-Custom', 'custom')
        && $request->hasHeader('Content-Type', 'application/json')
        && $request->hasHeader('adyen-library-name', 'adyen-php-api-library')
        && $request->hasHeader('User-Agent', 'adyen-php-api-library/'.Client::LIB_VERSION));
});

it('sends a request without a body', function (): void {
    Http::fake(['checkout-test.adyen.com/v71/sessions/*' => Http::response(['status' => 'completed'])]);

    $result = (new AdyenHttpClient)->requestHttp(
        adyenHttpClientService(),
        'https://checkout-test.adyen.com/v71/sessions/CS-1?sessionResult=result',
        null,
        'get'
    );

    expect($result)->toBe(['status' => 'completed']);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->body() === ''
        && ! $request->hasHeader('Idempotency-Key'));
});

it('throws the error Adyen returns', function (): void {
    Http::fake(['*' => Http::response([
        'status' => 422,
        'errorCode' => '14_030',
        'message' => 'Return URL is missing.',
        'errorType' => 'validation',
        'pspReference' => 'PSP-ERROR',
    ], 422)]);

    try {
        (new AdyenHttpClient)->requestHttp(adyenHttpClientService(), 'https://checkout-test.adyen.com/v71/sessions', [], 'post');

        $this->fail('No exception was thrown.');
    } catch (AdyenException $exception) {
        expect($exception->getMessage())->toBe('Return URL is missing.')
            ->and($exception->getCode())->toBe(422)
            ->and($exception->getAdyenErrorCode())->toBe('14_030')
            ->and($exception->getErrorType())->toBe('validation')
            ->and($exception->getPspReference())->toBe('PSP-ERROR');
    }
});

it('throws the response of an error that is not an Adyen error', function (): void {
    Http::fake(['*' => Http::response('Service Unavailable', 503)]);

    expect(fn (): array => (new AdyenHttpClient)->requestHttp(adyenHttpClientService(), 'https://checkout-test.adyen.com/v71/sessions', [], 'post'))
        ->toThrow(AdyenException::class, 'Service Unavailable');
});

it('throws a connection exception when Adyen cannot be reached', function (): void {
    Http::fake(fn () => throw new HttpConnectionException('cURL error 28: Operation timed out'));

    expect(fn (): array => (new AdyenHttpClient)->requestHttp(adyenHttpClientService(), 'https://checkout-test.adyen.com/v71/sessions', [], 'post'))
        ->toThrow(ConnectionException::class, 'Could not connect to Adyen (https://checkout-test.adyen.com/v71/sessions): cURL error 28: Operation timed out');
});

/**
 * Send a request through the Adyen HTTP client while a checkout page is open, and ignore the exception of a failed call.
 */
function sendLoggedAdyenRequest(): void
{
    app()->instance('request', HttpRequest::create('/checkout/payment', 'GET', [
        'checkoutId' => 'co-123',
        'itineraryId' => 'it-456',
        'origin' => 'ibe',
        'lang' => 'de',
    ]));

    try {
        (new AdyenHttpClient)->requestHttp(
            adyenHttpClientService(),
            'https://checkout-test.adyen.com/v71/sessions',
            ['reference' => 'transaction-1', 'amount' => ['currency' => 'EUR', 'value' => 19990]],
            'post',
            ['idempotencyKey' => 'session-transaction-1']
        );
    } catch (AdyenException|ConnectionException) {
        // The failed call is logged before the exception is thrown.
    }
}

/**
 * @return array<int, array{message: string, level: string, context: array<string, mixed>}>
 */
function adyenLogRecords(TestHandler $logs): array
{
    return array_map(fn (LogRecord $record): array => [
        'message' => $record->message,
        'level' => $record->level->getName(),
        'context' => $record->context,
    ], $logs->getRecords());
}

it('logs the request and the response with the checkout parameters and without the API key', function (): void {
    $logs = captureHttpLogs();
    Http::fake(['*' => Http::response(['id' => 'CS-1', 'sessionData' => 'Ab02b4c...'], 201)]);

    sendLoggedAdyenRequest();

    [$request, $response] = adyenLogRecords($logs);

    expect(adyenLogRecords($logs))->toHaveCount(2)
        ->and([$request['message'], $request['level']])->toBe(['checkout-to-adyen', 'INFO'])
        ->and([$response['message'], $response['level']])->toBe(['adyen-to-checkout', 'INFO'])
        ->and($request['context'])->toMatchArray(['checkoutId' => 'co-123', 'itineraryId' => 'it-456', 'origin' => 'ibe', 'lang' => 'de', 'direction' => 'outgoing'])
        ->and($response['context'])->toMatchArray(['checkoutId' => 'co-123', 'direction' => 'outgoing'])
        ->and($request['context']['adyen'])->toBe(['service' => PaymentsApi::class])
        ->and($response['context']['adyen'])->toBe(['service' => PaymentsApi::class])
        ->and($request['context']['correlation_id'])->toBe($response['context']['correlation_id'])
        ->and($request['context']['http']['method'])->toBe('POST')
        ->and($request['context']['http']['url'])->toBe('https://checkout-test.adyen.com/v71/sessions')
        ->and($request['context']['http']['headers']['x-api-key'])->toBe('[REDACTED]')
        ->and($request['context']['http']['headers']['Idempotency-Key'])->toBe('session-transaction-1')
        ->and($request['context']['http']['body'])->toBe(['reference' => 'transaction-1', 'amount' => ['currency' => 'EUR', 'value' => 19990]])
        ->and($response['context']['http']['status'])->toBe(201)
        ->and($response['context']['http']['body'])->toBe(['id' => 'CS-1', 'sessionData' => 'Ab02b4c...'])
        ->and(json_encode(adyenLogRecords($logs)))->not->toContain('usd-api-key');
});

it('logs an error response of Adyen with its status and body', function (int $status, string $level): void {
    $logs = captureHttpLogs();
    Http::fake(['*' => Http::response(['status' => $status, 'errorCode' => '000', 'message' => 'HTTP Status Response - Unauthorized', 'errorType' => 'security'], $status)]);

    sendLoggedAdyenRequest();

    [$request, $response] = adyenLogRecords($logs);

    expect(adyenLogRecords($logs))->toHaveCount(2)
        ->and($request['message'])->toBe('checkout-to-adyen')
        ->and([$response['message'], $response['level']])->toBe(['adyen-to-checkout', $level])
        ->and($response['context']['http']['status'])->toBe($status)
        ->and($response['context']['http']['body']['errorCode'])->toBe('000');
})->with([
    'client error' => [401, 'WARNING'],
    'server error' => [500, 'ERROR'],
]);

it('logs a call that cannot reach Adyen as a failure', function (): void {
    $logs = captureHttpLogs();
    Http::fake(fn () => throw new HttpConnectionException('cURL error 28: Operation timed out'));

    sendLoggedAdyenRequest();

    [$request, $failure] = adyenLogRecords($logs);

    expect(adyenLogRecords($logs))->toHaveCount(2)
        ->and($request['message'])->toBe('checkout-to-adyen')
        ->and([$failure['message'], $failure['level']])->toBe(['adyen-failed', 'ERROR'])
        ->and($failure['context'])->toMatchArray(['checkoutId' => 'co-123', 'direction' => 'outgoing'])
        ->and($failure['context']['http']['url'])->toBe('https://checkout-test.adyen.com/v71/sessions')
        ->and(json_encode($failure['context']))->toContain('Operation timed out');
});

it('logs Adyen calls in the same format as the Saloon integrations', function (): void {
    $logs = captureHttpLogs();
    Http::fake(['*' => Http::response(['result' => ['code' => '000.100.110']], 200)]);
    MockClient::global([OppwaStatusRequest::class => MockResponse::make(['result' => ['code' => '000.100.110']], 200)]);
    app()->instance('request', HttpRequest::create('/checkout/payment', 'GET', ['checkoutId' => 'co-123', 'itineraryId' => 'it-456', 'origin' => 'ibe', 'lang' => 'de']));

    OppwaConnector::make()->checkout()->status('v1/checkouts/checkout-1/payment');
    $saloon = adyenLogRecords($logs);

    $logs->reset();
    sendLoggedAdyenRequest();
    $adyen = adyenLogRecords($logs);

    // The integrations only differ in the key that names their caller: "saloon" with the connector
    // and request, or "adyen" with the service. "mocked" is only added to mocked Saloon responses.
    $shape = fn (array $record): array => [
        'level' => $record['level'],
        'context' => array_values(array_diff(array_keys($record['context']), ['saloon', 'adyen', 'mocked'])),
        'http' => array_keys($record['context']['http']),
    ];

    expect(array_column($saloon, 'message'))->toBe(['checkout-to-oppwa', 'oppwa-to-checkout'])
        ->and(array_column($adyen, 'message'))->toBe(['checkout-to-adyen', 'adyen-to-checkout'])
        ->and(array_map($shape, $adyen))->toBe(array_map($shape, $saloon))
        ->and(array_keys($saloon[0]['context']['saloon']))->toBe(['connector', 'request'])
        ->and($adyen[0]['context']['adyen'])->toBe(['service' => PaymentsApi::class]);
});
