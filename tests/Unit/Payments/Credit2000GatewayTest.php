<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Credit2000\Requests\Credit2000SoapRequest;
use Nezasa\Checkout\Integrations\Credit2000\Support\Credit2000Xml;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Gateways\Credit2000\Credit2000Gateway;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    MockClient::destroyGlobal();
});

function c2kTransaction(string $id = '01C2KTEST'): Transaction
{
    $transaction = new Transaction;
    $transaction->id = $id;
    $transaction->amount = 10;
    $transaction->currency = 'ILS';

    return $transaction;
}

function c2kRequest(Transaction $transaction, array $query): Request
{
    $request = Request::create('/checkout/result/'.$transaction->id, 'GET', $query);
    $route = new Route(['GET'], '/checkout/result/{transaction}', []);
    $route->bind($request);
    $route->setParameter('transaction', $transaction);
    $request->setRouteResolver(fn (): Route => $route);

    return $request;
}

function c2kPrepareData(Transaction $transaction, string $currency = 'ILS', float $amount = 10.00): PaymentPrepareData
{
    return new PaymentPrepareData(
        transaction: $transaction,
        returnUrl: Uri::of('https://checkout-staging.tourismo-filipino.com/checkout/result/'.$transaction->id.'?checkoutId=chk_1&itineraryId=it_1&origin=ibe&lang=he'),
        cancelUrl: Uri::of('https://checkout-staging.tourismo-filipino.com/checkout/details'),
        contact: new ContactInfoPayloadEntity(firstName: 'Dana', lastName: 'Cohen', email: 'dana@example.com', localIdNumber: '203269535'),
        price: new Price($amount, $currency),
        checkoutId: 'chk_1',
        itineraryId: 'it_1',
        lang: 'he',
    );
}

function c2kPersistent(string $order = '01C2KOK'): array
{
    return [
        'order' => $order,
        'product_id' => Credit2000Xml::productId($order),
        'amount_subunits' => 1000,
        'total_pyment' => '1000',
        'currency' => 'ILS',
        'currency_code' => '1',
        'checkout_id' => 'chk_1',
        'client_name' => 'Cohen/Dana',
        'tz_number' => '203269535',
        'prepare_action_type' => '5',
    ];
}

function c2kConfig(): void
{
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
}

function c2kProSoapBody(array $overrides = []): string
{
    // Successful live-shaped Pro baseline (ActionType 5): return_Code=000, real Approve,
    // populated ValidDate, token present. Pro uID is often empty on the live terminal.
    $defaults = [
        'product_Id' => Credit2000Xml::productId('01C2KOK'),
        'total_Pyment' => '1000',
        'currency' => '1',
        'action_Type' => '5',
        'uID' => '',
        'return_Code' => '000',
        'Approve' => '7899627',
        'ValidDate' => '0632',
        'token' => '9101111111116951',
        'cardType' => '4',
        'mutag' => '4',
        'include_token' => true,
    ];
    $data = array_merge($defaults, $overrides);

    $tokenXml = ($data['include_token'] ?? true)
        ? '<token>'.$data['token'].'</token>'
        : '<token />';

    return '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
        .'<getTokenAndApproveProResponse xmlns="http://tempuri.org/">'
        .'<getTokenAndApproveProResult>'
        .'<product_Id>'.$data['product_Id'].'</product_Id>'
        .'<total_Pyment>'.$data['total_Pyment'].'</total_Pyment>'
        .'<currency>'.$data['currency'].'</currency>'
        .'<action_Type>'.$data['action_Type'].'</action_Type>'
        .'<uID>'.$data['uID'].'</uID>'
        .'<return_Code>'.$data['return_Code'].'</return_Code>'
        .'<Approve>'.$data['Approve'].'</Approve>'
        .'<ValidDate>'.$data['ValidDate'].'</ValidDate>'
        .'</getTokenAndApproveProResult>'
        .$tokenXml
        .'<cardType>'.$data['cardType'].'</cardType>'
        .'<mutag>'.$data['mutag'].'</mutag>'
        .'</getTokenAndApproveProResponse>'
        .'</soap:Body></soap:Envelope>';
}

/** Failed live baseline: binding echo with SendParam placeholders and no token. */
function c2kProFailedSoapBody(array $overrides = []): string
{
    return c2kProSoapBody(array_merge([
        'return_Code' => '123',
        'Approve' => '0000000',
        'ValidDate' => '',
        'include_token' => false,
        'token' => '',
        'cardType' => '',
        'mutag' => '0',
    ], $overrides));
}

it('reports inactive by default', function (): void {
    Config::set('checkout.integrations.credit2000.active', false);

    expect(Credit2000Gateway::isActive())->toBeFalse();
});

it('reports active when enabled', function (): void {
    c2kConfig();

    expect(Credit2000Gateway::isActive())->toBeTrue()
        ->and(Credit2000Gateway::name())->toBe('Credit2000')
        ->and(Credit2000Gateway::isTokenized())->toBeFalse();
});

it('builds a 16-digit product id', function (): void {
    expect(Credit2000Xml::productId('01C2KTEST'))->toHaveLength(16)
        ->and(Credit2000Xml::productId('01C2KTEST'))->toMatch('/^\d{16}$/');
});

it('prepares a hosted payment redirect url', function (): void {
    c2kConfig();

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><SendParamToCredit2000Response xmlns="http://tempuri.org/"><SendParamToCredit2000Result>https://www.credit2000.co.il/pay/abc123</SendParamToCredit2000Result></SendParamToCredit2000Response></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction();
    $gateway = new Credit2000Gateway;
    $init = $gateway->prepare(c2kPrepareData($transaction));

    expect($init->isAvailable)->toBeTrue()
        ->and((string) $gateway->getRedirectUrl($init))->toBe('https://www.credit2000.co.il/pay/abc123')
        ->and($init->persistentData['total_pyment'])->toBe('1000')
        ->and($init->persistentData['prepare_action_type'])->toBe('5');

    $mock->assertSent(function (Credit2000SoapRequest $request): bool {
        $bodyXml = (new ReflectionClass($request))->getProperty('bodyXml');

        return str_contains((string) $bodyXml->getValue($request), '<club>0</club>');
    });
});

it('serializes SendParam club as numeric zero', function (): void {
    c2kConfig();

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><SendParamToCredit2000Response xmlns="http://tempuri.org/"><SendParamToCredit2000Result>https://www.credit2000.co.il/pay/abc123</SendParamToCredit2000Result></SendParamToCredit2000Response></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    $mock->assertSent(function (Credit2000SoapRequest $request): bool {
        $bodyXml = (new ReflectionClass($request))->getProperty('bodyXml');
        $body = (string) $bodyXml->getValue($request);

        return str_contains($body, '<club>0</club>')
            && ! str_contains($body, '<club></club>');
    });
});

it('marks prepare unavailable for unsupported currency', function (): void {
    c2kConfig();

    $gateway = new Credit2000Gateway;
    $init = $gateway->prepare(c2kPrepareData(c2kTransaction(), 'GBP'));

    expect($init->isAvailable)->toBeFalse();
});

it('rejects prepare when prepare_action_type is test mode 2', function (): void {
    c2kConfig();
    Config::set('checkout.integrations.credit2000.prepare_action_type', '2');

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'should-not-be-called', status: 500),
    ]);

    $init = (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    expect($init->isAvailable)->toBeFalse();
    $mock->assertNothingSent();
});

it('rejects prepare when prepare_action_type is page charge 4', function (): void {
    c2kConfig();
    Config::set('checkout.integrations.credit2000.prepare_action_type', '4');

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'should-not-be-called', status: 500),
    ]);

    $init = (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    expect($init->isAvailable)->toBeFalse();
    $mock->assertNothingSent();
});

it('prepares successfully when prepare_action_type is approval 5', function (): void {
    c2kConfig();
    Config::set('checkout.integrations.credit2000.prepare_action_type', '5');

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><SendParamToCredit2000Response xmlns="http://tempuri.org/"><SendParamToCredit2000Result>https://www.credit2000.co.il/pay/at5</SendParamToCredit2000Result></SendParamToCredit2000Response></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $init = (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    expect($init->isAvailable)->toBeTrue()
        ->and($init->persistentData['prepare_action_type'])->toBe('5')
        ->and($init->persistentData)->not->toHaveKey('charged_on_page');

    $mock->assertSent(function (Credit2000SoapRequest $request): bool {
        $bodyXml = (new ReflectionClass($request))->getProperty('bodyXml');
        $body = (string) $bodyXml->getValue($request);

        return str_contains($body, '<action_Type>5</action_Type>')
            && ! str_contains($body, '<action_Type>4</action_Type>');
    });
});

it('refuses capture when prepare_action_type was test mode 2', function (): void {
    c2kConfig();

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'should-not-be-called', status: 500),
    ]);

    $persistent = c2kPersistent();
    $persistent['prepare_action_type'] = '2';

    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture(c2kRequest(c2kTransaction('01C2KOK'), []), $persistent, $resultData);

    expect($capture->isSuccessful)->toBeFalse()
        ->and(data_get($capture->persistentData, 'capture_error'))->toBe('test_mode_prepare_cannot_charge');
    $mock->assertNothingSent();
});

it('refuses capture when prepare_action_type was page charge 4', function (): void {
    c2kConfig();

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(body: 'should-not-be-called', status: 500),
    ]);

    $persistent = c2kPersistent();
    $persistent['prepare_action_type'] = '4';

    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture(c2kRequest(c2kTransaction('01C2KOK'), []), $persistent, $resultData);

    expect($capture->isSuccessful)->toBeFalse()
        ->and(data_get($capture->persistentData, 'capture_error'))->toBe('unsupported_prepare_action_type');
    $mock->assertNothingSent();
});

it('authorizes with callback uid and matching getTokenAndApprovePro data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
        'checkoutId' => 'chk_1',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeTrue()
        ->and(data_get($result->resultData, 'credit2000.token'))->toBe('9101111111116951')
        ->and(data_get($result->resultData, 'credit2000.approveNum'))->toBe('7899627')
        ->and(data_get($result->resultData, 'credit2000.validDate'))->toBe('0632')
        ->and(data_get($result->resultData, 'credit2000.return_Code'))->toBe('000');
});

it('rejects authorize when provider product_Id mismatches prepare data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['product_Id' => '0000000000000001']),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('product_id_mismatch');
});

it('rejects authorize when provider total_Pyment mismatches prepare data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['total_Pyment' => '2000']),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('amount_mismatch');
});

it('rejects authorize when provider currency mismatches prepare data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['currency' => '2']),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('currency_mismatch');
});

it('rejects authorize when provider action_Type mismatches prepare data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['action_Type' => '4']),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('action_type_mismatch');
});

it('rejects authorize when Pro return_Code is not 000', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProFailedSoapBody(),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBeIn(['token_missing', 'return_code_not_approved']);
});

it('rejects authorize when Pro return_Code is non-000 even with a token', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['return_Code' => '123']),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'uid-1']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('return_code_not_approved');
});

it('rejects authorize when Approve is the SendParam placeholder', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['Approve' => '0000000']),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'uid-1']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('approve_placeholder');
});

it('rejects authorize when Approve is missing', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['Approve' => '']),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'uid-1']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('approve_missing');
});

it('rejects authorize when ValidDate is missing or invalid for capture', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['ValidDate' => '']),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'uid-1']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('valid_date_missing');
});

it('rejects authorize when Pro token is missing', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(['include_token' => false, 'token' => '']),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => 'uid-1']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('token_missing');
});

it('accepts a successful live-shaped Pro response without requiring Pro uID', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody([
                'uID' => '',
                'return_Code' => '000',
                'Approve' => '7899627',
                'ValidDate' => '0632',
                'cardType' => '4',
                'mutag' => '4',
            ]),
            status: 200,
        ),
    ]);

    $result = (new Credit2000Gateway)->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => '2746a108-9caf-4cb2-b33c-2a36c912e59e']),
        c2kPersistent()
    );

    expect($result->isSuccessful)->toBeTrue()
        ->and(data_get($result->resultData, 'credit2000.uid'))->toBe('2746a108-9caf-4cb2-b33c-2a36c912e59e')
        ->and(data_get($result->resultData, 'credit2000.approveNum'))->toBe('7899627')
        ->and(data_get($result->resultData, 'credit2000.validDate'))->toBe('0632');
});

it('rejects authorize when uid is missing', function (): void {
    c2kConfig();

    $transaction = c2kTransaction();
    $request = c2kRequest($transaction, ['TotalPayment' => '10']);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeFalse()
        ->and(data_get($result->resultData, 'reason'))->toBe('missing_uid');
});

it('captures via CreditXML charge', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><CreditXMLResponse xmlns="http://tempuri.org/"><CreditXMLResult>000</CreditXMLResult><returnCode>000</returnCode><confirmationNumber>998877</confirmationNumber></CreditXMLResponse></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, []);
    $resultData = [
        'credit2000' => [
            'uid' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture($request, c2kPersistent(), $resultData);

    expect($capture->isSuccessful)->toBeTrue()
        ->and(data_get($capture->persistentData, 'capture.returnCode'))->toBe('000');
});

it('authorizes ActionType 5 then captures via CreditXML actionType 4', function (): void {
    c2kConfig();

    $uid = 'e14643ab-562a-4a64-a59a-49a9efa978e9';
    $soapCall = 0;

    $mock = MockClient::global([
        Credit2000SoapRequest::class => function () use (&$soapCall): MockResponse {
            $soapCall++;

            if ($soapCall === 1) {
                return MockResponse::make(body: c2kProSoapBody(), status: 200);
            }

            return MockResponse::make(
                body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><CreditXMLResponse xmlns="http://tempuri.org/"><CreditXMLResult>000</CreditXMLResult><returnCode>000</returnCode><confirmationNumber>998877</confirmationNumber></CreditXMLResponse></soap:Body></soap:Envelope>',
                status: 200,
            );
        },
    ]);

    $gateway = new Credit2000Gateway;
    $authorized = $gateway->authorize(
        c2kRequest(c2kTransaction('01C2KOK'), ['params' => $uid]),
        c2kPersistent()
    );

    expect($authorized->isSuccessful)->toBeTrue()
        ->and(data_get($authorized->resultData, 'credit2000.token'))->toBe('9101111111116951');

    $captured = $gateway->capture(
        c2kRequest(c2kTransaction('01C2KOK'), []),
        c2kPersistent(),
        $authorized->resultData
    );

    expect($captured->isSuccessful)->toBeTrue()
        ->and(data_get($captured->persistentData, 'capture.returnCode'))->toBe('000')
        ->and($soapCall)->toBe(2);

    $mock->assertSent(function (Credit2000SoapRequest $request): bool {
        $bodyXml = (new ReflectionClass($request))->getProperty('bodyXml');
        $body = (string) $bodyXml->getValue($request);

        return str_contains($body, '<actionType>4</actionType>');
    });
});

it('leaves uncaptured approval to expire on abort without inventing a provider release', function (): void {
    c2kConfig();

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, []);
    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $abort = (new Credit2000Gateway)->abort($request, c2kPersistent(), $resultData);

    expect($abort->isSuccessful)->toBeTrue()
        ->and(data_get($abort->persistentData, 'cancel.mode'))->toBe('uncaptured_approval_left_to_expire')
        ->and(data_get($abort->persistentData, 'cancel.returnCode'))->toBeNull();
});

it('blocks capture after uncaptured approval was left to expire on abort', function (): void {
    c2kConfig();

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, []);
    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
        'cancel' => [
            'mode' => 'uncaptured_approval_left_to_expire',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture($request, c2kPersistent(), $resultData);

    expect($capture->isSuccessful)->toBeFalse()
        ->and(data_get($capture->persistentData, 'capture_error'))->toBe('already_aborted');
});

it('extracts payment url only from the SendParam result element', function (): void {
    $ok = '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
        .'<SendParamToCredit2000Response xmlns="http://tempuri.org/">'
        .'<SendParamToCredit2000Result>https://www.credit2000.co.il/pay/ok</SendParamToCredit2000Result>'
        .'</SendParamToCredit2000Response></soap:Body></soap:Envelope>';

    $fault = '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
        .'<soap:Fault><faultcode>soap:Server</faultcode><faultstring>boom</faultstring>'
        .'<detail>https://evil.example/phish</detail></soap:Fault></soap:Body></soap:Envelope>';

    $unrelated = '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
        .'<OtherResponse xmlns="http://tempuri.org/"><note>see https://www.credit2000.co.il/unrelated</note></OtherResponse>'
        .'</soap:Body></soap:Envelope>';

    expect(Credit2000Xml::extractPaymentUrl($ok))->toBe('https://www.credit2000.co.il/pay/ok')
        ->and(Credit2000Xml::extractPaymentUrl($fault))->toBeNull()
        ->and(Credit2000Xml::extractPaymentUrl($unrelated))->toBeNull();
});

it('marks prepare unavailable when Credit2000 returns no payment url', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
                .'<soap:Fault><faultcode>soap:Server</faultcode><faultstring>error</faultstring>'
                .'<detail>https://evil.example/phish</detail></soap:Fault></soap:Body></soap:Envelope>',
            status: 500,
        ),
    ]);

    $init = (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    expect($init->isAvailable)->toBeFalse();
});

it('rejects SOAP Fault and unrelated urls as prepare payment redirects', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
                .'<OtherResponse xmlns="http://tempuri.org/"><url>https://www.credit2000.co.il/not-the-result</url></OtherResponse>'
                .'</soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $init = (new Credit2000Gateway)->prepare(c2kPrepareData(c2kTransaction()));

    expect($init->isAvailable)->toBeFalse();
});

it('does not persist raw SOAP bodies in authorize result data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: c2kProSoapBody(),
            status: 200,
        ),
    ]);

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, [
        'params' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
    ]);

    $result = (new Credit2000Gateway)->authorize($request, c2kPersistent());

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->resultData)->not->toHaveKey('token')
        ->and(data_get($result->resultData, 'credit2000.token'))->toBe('9101111111116951')
        ->and(json_encode($result->resultData))->not->toContain('getTokenAndApproveProResponse')
        ->and(json_encode($result->resultData))->not->toContain('<soap');
});

it('does not persist raw SOAP bodies in capture result data', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><CreditXMLResponse xmlns="http://tempuri.org/"><CreditXMLResult>000</CreditXMLResult><returnCode>000</returnCode><confirmationNumber>998877</confirmationNumber></CreditXMLResponse></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $resultData = [
        'credit2000' => [
            'uid' => 'e14643ab-562a-4a64-a59a-49a9efa978e9',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture(
        c2kRequest(c2kTransaction('01C2KOK'), []),
        c2kPersistent(),
        $resultData
    );

    expect($capture->isSuccessful)->toBeTrue()
        ->and(data_get($capture->persistentData, 'capture'))->not->toHaveKey('raw')
        ->and(json_encode($capture->persistentData))->not->toContain('<soap')
        ->and(json_encode($capture->persistentData))->not->toContain('CreditXMLResponse');
});

it('fails capture when CreditXML returnCode is not successful', function (): void {
    c2kConfig();

    MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><CreditXMLResponse xmlns="http://tempuri.org/"><CreditXMLResult>051</CreditXMLResult><returnCode>051</returnCode></CreditXMLResponse></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
    ];

    $capture = (new Credit2000Gateway)->capture(
        c2kRequest(c2kTransaction('01C2KOK'), []),
        c2kPersistent(),
        $resultData
    );

    expect($capture->isSuccessful)->toBeFalse()
        ->and(data_get($capture->persistentData, 'capture.returnCode'))->toBe('051')
        ->and(data_get($capture->persistentData, 'capture'))->not->toHaveKey('raw');
});

it('refunds via CreditXML actionType 7 when aborting a charged payment', function (): void {
    c2kConfig();

    $mock = MockClient::global([
        Credit2000SoapRequest::class => MockResponse::make(
            body: '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><CreditXMLResponse xmlns="http://tempuri.org/"><CreditXMLResult>000</CreditXMLResult><returnCode>000</returnCode><confirmationNumber>refund-1</confirmationNumber></CreditXMLResponse></soap:Body></soap:Envelope>',
            status: 200,
        ),
    ]);

    $resultData = [
        'credit2000' => [
            'uid' => 'uid-1',
            'token' => '9101111111116951',
            'approveNum' => '1234567',
            'validDate' => '0729',
            'cardType' => '1',
            'customerId' => '9999',
        ],
        'capture' => [
            'returnCode' => '000',
            'mode' => 'creditxml_charge',
            'confirmationNumber' => '1234567',
        ],
    ];

    $abort = (new Credit2000Gateway)->abort(
        c2kRequest(c2kTransaction('01C2KOK'), []),
        c2kPersistent(),
        $resultData
    );

    expect($abort->isSuccessful)->toBeTrue()
        ->and(data_get($abort->persistentData, 'cancel.returnCode'))->toBe('000')
        ->and(data_get($abort->persistentData, 'cancel'))->not->toHaveKey('raw');

    $mock->assertSent(function (Credit2000SoapRequest $request): bool {
        $bodyXml = (new ReflectionClass($request))->getProperty('bodyXml');

        return str_contains((string) $bodyXml->getValue($request), '<actionType>7</actionType>');
    });
});

it('builds a Nezasa transaction payload from capture result data', function (): void {
    c2kConfig();

    $transaction = c2kTransaction('01C2KOK');
    $request = c2kRequest($transaction, []);
    $capture = new CaptureResult(
        isSuccessful: true,
        persistentData: [
            'capture' => [
                'returnCode' => '000',
                'confirmationNumber' => 'conf-42',
            ],
        ]
    );

    $payload = (new Credit2000Gateway)->makeNezasaTransactionPayload($request, $capture);

    expect($payload->externalRefId)->toBe('conf-42')
        ->and($payload->paymentMethodName)->toBe('Credit2000')
        ->and($payload->amount->amount)->toBe(10.0)
        ->and($payload->amount->currency)->toBe('ILS');
});
