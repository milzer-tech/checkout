<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\AddCustomInsuranceRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Payment\CreatePaymentTransactionRequest;
use Nezasa\Checkout\Integrations\Vertical\Requests\PurchaseEventRequest;
use Nezasa\Checkout\Listeners\VerticalInsuranceListener;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Invoice\InvoiceGateway;
use Nezasa\Checkout\Payments\Gateways\Stripe\StripeGateway;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

function verticalListenerCheckout(array $insurance): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => 'checkout-123',
        'itinerary_id' => 'itinerary-456',
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'email' => 'jane@example.test',
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
                'city' => 'Berlin',
                'postalCode' => '10115',
                'street1' => 'Main Street',
                'street2' => '42',
            ],
            'insurance' => $insurance,
        ],
    ]);
}

function verticalListenerTransaction(Checkout $checkout, string $gateway): Transaction
{
    return Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => $gateway,
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
        'result_data' => [
            'session' => [
                'payment_intent' => 'pi_main',
                'customer' => 'cus_main',
            ],
        ],
    ]);
}

/**
 * Answer the Stripe calls of the listener without reaching Stripe: it reads the payment method of the paid
 * payment intent, clones it to the connected account of Vertical, and charges the insurance with the clone.
 */
function fakeStripeForVerticalListener(): void
{
    ApiRequestor::setHttpClient(new class implements ClientInterface
    {
        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            $body = match ($method.' '.parse_url((string) $absUrl, PHP_URL_PATH)) {
                'get /v1/payment_intents/pi_main' => ['id' => 'pi_main', 'object' => 'payment_intent', 'payment_method' => 'pm_main'],
                'post /v1/payment_methods' => ['id' => 'pm_clone', 'object' => 'payment_method'],
                'post /v1/payment_intents' => ['id' => 'pi_vertical', 'object' => 'payment_intent'],
            };

            return [json_encode($body), 200, []];
        }
    });
}

beforeEach(function (): void {
    Config::set('checkout.integrations.invoice.name', 'Invoice');
    Config::set('checkout.integrations.stripe.name', 'Credit Card');
    Config::set('checkout.integrations.stripe.secret_key', 'sk_test_not_used');
    Config::set('checkout.insurance.vertical.active', true);
    Config::set('checkout.insurance.vertical.connected_account_id', 'acct_test');
});

afterEach(function (): void {
    ApiRequestor::setHttpClient(null);
});

it('describes the purchased Vertical insurance on Nezasa with the ViCoverage notice in Markdown', function (): void {
    fakeStripeForVerticalListener();

    $mockClient = MockClient::global([
        PurchaseEventRequest::class => MockResponse::make([
            'id' => 'purchase-123',
            'policy_number' => 'POL-123',
            'total' => 2450,
            'currency' => 'EUR',
            'product' => ['promotional_header' => 'Travel Protection'],
        ]),
        AddCustomInsuranceRequest::class => MockResponse::make(['created' => true]),
        CreatePaymentTransactionRequest::class => MockResponse::make(['id' => 'nezasa-transaction']),
    ]);

    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'quote-123',
        'title' => 'Vertical quote',
        'price' => ['amount' => 24.5, 'currency' => 'EUR'],
        'coverage' => [],
    ];
    $bucket[InsuranceCheckoutData::META] = [
        'currency' => 'eur',
        'total' => 2450,
    ];

    $checkout = verticalListenerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );
    $transaction = verticalListenerTransaction($checkout, StripeGateway::name());

    (new VerticalInsuranceListener)->handle(new ItineraryBookingSucceededEvent($transaction));

    $mockClient->assertSent(function (mixed $request): bool {
        if (! $request instanceof AddCustomInsuranceRequest) {
            return false;
        }

        expect($request->body()->all())
            ->toMatchArray([
                'name' => 'Travel Protection',
                'bookingStatus' => 'Booked',
                'supplierName' => 'ViCoverage',
                'supplierConfirmationNumber' => 'POL-123',
                'description' => 'You have purchased trip protection and will receive information about your coverage in a separate email from ViCoverage. You can visit [customers.vicoverage.com](https://customers.vicoverage.com/login) to find details or file a claim.',
            ]);

        return true;
    });
});

it('does not process Vertical insurance unless the successful payment gateway is Stripe', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'quote-123',
        'title' => 'Vertical quote',
        'price' => ['amount' => 24.5, 'currency' => 'EUR'],
        'coverage' => [],
    ];
    $bucket[InsuranceCheckoutData::META] = [
        'currency' => 'eur',
        'total' => 2450,
    ];

    $checkout = verticalListenerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );
    $transaction = verticalListenerTransaction($checkout, InvoiceGateway::name());

    (new VerticalInsuranceListener)->handle(new ItineraryBookingSucceededEvent($transaction));

    $transaction->refresh();

    expect($transaction->result_data)->toBe([
        'session' => [
            'payment_intent' => 'pi_main',
            'customer' => 'cus_main',
        ],
    ]);
});

it('does not process Vertical insurance for Stripe when no quote was selected', function (): void {
    $checkout = verticalListenerCheckout(InsuranceCheckoutData::emptyInsuranceBucket());
    $transaction = verticalListenerTransaction($checkout, StripeGateway::name());

    (new VerticalInsuranceListener)->handle(new ItineraryBookingSucceededEvent($transaction));

    $transaction->refresh();

    expect($transaction->result_data)->toBe([
        'session' => [
            'payment_intent' => 'pi_main',
            'customer' => 'cus_main',
        ],
    ]);
});
