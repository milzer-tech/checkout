<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Credit2000\Connectors\Credit2000Connector;
use Nezasa\Checkout\Integrations\Credit2000\Requests\Credit2000SoapRequest;
use Saloon\Helpers\URLHelper;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    MockClient::destroyGlobal();

    Config::set('checkout.integrations.credit2000', [
        'active' => true,
        'name' => 'Credit2000',
        'base_url' => 'https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx',
        'vendor_name' => 'cuTEST',
        'company_key' => 'DCSTEST==',
        'lang' => 'he',
        'prepare_action_type' => '5',
        'purchase_type' => '1',
    ]);
});

it('documents that Saloon joining base asmx URL with slash endpoint yields a trailing slash', function (): void {
    $base = 'https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx';

    expect(URLHelper::join($base, '/'))->toBe($base.'/');
    expect(URLHelper::join($base, ''))->toBe($base);
});

it('resolves the final SOAP request URL to the exact asmx path without a trailing slash', function (): void {
    $expected = 'https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx';

    $connector = new Credit2000Connector;
    expect($connector->resolveBaseUrl())->toBe($expected);

    $request = new Credit2000SoapRequest(
        'http://tempuri.org/SendParamToCredit2000',
        '<?xml version="1.0"?><soap:Envelope/>',
    );
    expect($request->resolveEndpoint())->toBe('');

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'ok', status: 200),
    ]);

    $pending = $connector->createPendingRequest($request);
    $url = $pending->getUrl();

    expect($url)->toBe($expected)
        ->and($url)->not->toEndWith('asmx/')
        ->and(str_ends_with($url, '/'))->toBeFalse();
});

it('keeps the exact asmx URL when config base_url has a trailing slash', function (): void {
    Config::set(
        'checkout.integrations.credit2000.base_url',
        'https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx/'
    );

    $expected = 'https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx';
    $connector = new Credit2000Connector;
    $request = new Credit2000SoapRequest('http://tempuri.org/SendParamToCredit2000', '<x/>');

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'ok', status: 200),
    ]);

    expect($connector->createPendingRequest($request)->getUrl())->toBe($expected);
});
