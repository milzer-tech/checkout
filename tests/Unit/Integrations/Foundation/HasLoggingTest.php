<?php

declare(strict_types=1);

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Context;
use Milzer\HttpLogger\Core\HttpLogger;
use Milzer\HttpLogger\Core\LoggingOptions;
use Milzer\HttpLogger\Core\Writers\ImmediateWriter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Support\CheckoutLogContext;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

beforeEach(function (): void {
    $this->logs = new TestHandler;

    $this->app->instance(HttpLogger::class, new HttpLogger(
        logger: new Logger('test', [$this->logs]),
        options: new LoggingOptions,
        writer: new ImmediateWriter,
    ));

    MockClient::global(['*' => MockResponse::make(['ok' => true])]);
});

function sendPing(): void
{
    NezasaConnector::make()->send(new class extends Request
    {
        protected Method $method = Method::GET;

        public function resolveEndpoint(): string
        {
            return '/ping';
        }
    });
}

function swapRequest(HttpRequest $request): void
{
    app()->instance('request', $request);
}

it('adds the checkout query parameters to every outgoing log entry', function (): void {
    swapRequest(HttpRequest::create('/checkout/details', 'GET', [
        'checkoutId' => 'co-123',
        'itineraryId' => 'it-456',
        'origin' => 'ibe',
        'lang' => 'de',
        'rest-payment' => '1',
    ]));

    sendPing();

    $records = $this->logs->getRecords();

    expect($records)->toHaveCount(2)
        ->and(array_map(fn ($record): string => $record->message, $records))->toBe(['outgoing-request', 'outgoing-response']);

    foreach ($records as $record) {
        expect($record->context)->toMatchArray([
            'checkoutId' => 'co-123',
            'itineraryId' => 'it-456',
            'origin' => 'ibe',
            'lang' => 'de',
            'restPayment' => true,
        ]);
    }
});

it('reads the parameters from the component snapshot on Livewire update requests', function (): void {
    $request = HttpRequest::create('/livewire/update', 'POST', [
        'components' => [[
            'snapshot' => json_encode(['data' => [
                'checkoutId' => 'co-123',
                'itineraryId' => 'it-456',
                'origin' => 'app',
                'lang' => null,
                'restPayment' => false,
                'isExpanded' => true,
            ]]),
        ]],
    ]);
    $request->headers->set('X-Livewire', '1');
    swapRequest($request);

    expect(CheckoutLogContext::fromRequest($request))->toBe([
        'checkoutId' => 'co-123',
        'itineraryId' => 'it-456',
        'origin' => 'app',
        'restPayment' => false,
    ]);
});

it('prefers the parameters carried into a queued job through the context', function (): void {
    Context::addHidden(CheckoutLogContext::CONTEXT_KEY, ['checkoutId' => 'from-job']);

    sendPing();

    expect($this->logs->getRecords()[0]->context['checkoutId'])->toBe('from-job');
});

it('adds the parameters to the context of queued jobs', function (): void {
    swapRequest(HttpRequest::create('/checkout/details', 'GET', ['checkoutId' => 'co-123', 'origin' => 'ibe']));

    $dehydrated = Context::dehydrate();

    expect(unserialize($dehydrated['hidden'][CheckoutLogContext::CONTEXT_KEY]))->toBe(['checkoutId' => 'co-123', 'origin' => 'ibe']);
});

it('logs without checkout parameters outside a checkout request', function (): void {
    sendPing();

    expect($this->logs->getRecords()[0]->context)->not->toHaveKey('checkoutId');
});
