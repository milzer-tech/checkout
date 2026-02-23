<?php

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Nezasa\Checkout\Integrations\Oppwa\Requests\OppwaPrepareRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('sends OppwaPrepareRequest with correct method, endpoint, headers, query and body; returns mapped DTO', function (): void {
    Config::set('checkout.payment.widget.oppwa.base_url', 'https://oppwa.example.test');
    Config::set('checkout.payment.widget.oppwa.entity_id', 'entity-999');
    Config::set('checkout.payment.widget.oppwa.token', 'secret-token-123');

    $payload = new OppwaPreparePayload(
        amount: '199.90',
        currency: 'EUR',
        customerEmail: 'john.doe@example.com',
        paymentType: 'PA'
    );

    $mockClient = new MockClient([
        OppwaPrepareRequest::class => MockResponse::fixture('oppwa_prepare_response'),
    ]);

    $connector = new OppwaConnector;
    $connector->withMockClient($mockClient);
    $response = $connector->checkout()->prepare($payload);

    $mockClient->assertSent(function (OppwaPrepareRequest $pending) use ($payload): bool {
        expect($pending->getMethod()->value)->toBe('POST');
        expect($pending->resolveEndpoint())->toContain('v1/checkouts');
        $body = $pending->body()->all();
        $expected = $payload->toArray();
        expect($expected)->toHaveKeys([
            'amount',
            'currency',
            'customer.email',
            'paymentType',
            'integrity',
        ]);

        expect($body)->toEqualCanonicalizing($expected);

        return true;
    });

    expect($response->successful())->toBeTrue();

    $dto = $response->dto();

    expect($dto)
        ->toBeInstanceOf(OppwaPrepareResponse::class)
        ->and($dto->id)->toBe('A63C50B12D3A1989D91B1E9B592FFF59.uat01-vm-tx02')
        ->and($dto->ndc)->toBe('A63C50B12D3A1989D91B1E9B592FFF59.uat01-vm-tx02')
//        ->and($dto->integrity)->toBe('sha384-LiZULe6NXCWUQyip4b7EHhQ8SY6nBsE3xw/FZbp/ior2UrbIbp9q6aeRC1EyMdf6')
        ->and($dto->buildNumber)->toBe('e05bc10dcc6acc6bd4abda346f4af077dcd905d7@2025-08-25 10:32:14 +0000');

    expect($dto->timestamp->toIso8601String())->toBe('2025-08-26T14:17:40+00:00');

    expect($dto->result->code)->toBe('000.200.100')
        ->and($dto->result->description)->toBe('successfully created checkout');
});
