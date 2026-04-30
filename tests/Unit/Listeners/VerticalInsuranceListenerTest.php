<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Listeners\VerticalInsuranceListener;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Payments\Gateways\Invoice\InvoiceGateway;
use Nezasa\Checkout\Payments\Gateways\Stripe\StripeGateway;

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

beforeEach(function (): void {
    Config::set('checkout.integrations.invoice.name', 'Invoice');
    Config::set('checkout.integrations.stripe.name', 'Credit Card');
    Config::set('checkout.integrations.stripe.secret_key', 'sk_test_not_used');
    Config::set('checkout.insurance.vertical.active', true);
    Config::set('checkout.insurance.vertical.connected_account_id', 'acct_test');
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
