<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

function paymentResultPageForInsuranceTest(array $insurance, array $resultData = []): PaymentResultPage
{
    $checkout = Checkout::factory()->create([
        'data' => [
            'insurance' => $insurance,
        ],
    ]);

    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
        'result_data' => $resultData,
    ]);

    $itinerary = (new ReflectionClass(ItinerarySummary::class))->newInstanceWithoutConstructor();
    $itinerary->insurances = new Collection;

    $page = new PaymentResultPage;
    $page->transaction = $transaction;
    $page->itinerary = $itinerary;

    return $page;
}

function invokeProcessInsuranceData(PaymentResultPage $page): void
{
    $method = new ReflectionMethod(PaymentResultPage::class, 'processInsuranceData');
    $method->invoke($page);
}

it('shows selected insurance product on booking confirmation', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'ergo-offer',
        'title' => 'ERGO Travel Insurance',
        'price' => ['amount' => 25.0, 'currency' => 'EUR'],
        'coverage' => [],
    ];

    $page = paymentResultPageForInsuranceTest(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance'],
        ['insurance' => ['isSuccessful' => true]]
    );

    invokeProcessInsuranceData($page);

    expect($page->itinerary->insurances)->toHaveCount(1)
        ->and($page->itinerary->insurances->first()->name)->toBe('ERGO Travel Insurance')
        ->and($page->itinerary->insurances->first()->availability)->toBe(AvailabilityEnum::Booked);
});

it('shows declined insurance status on booking confirmation for contract providers', function (): void {
    $page = paymentResultPageForInsuranceTest(
        InsuranceCheckoutData::prepareInsuranceUpdate(
            InsuranceCheckoutData::declinedInsuranceBucket('ERGO')
        )['insurance']
    );

    invokeProcessInsuranceData($page);

    expect($page->itinerary->insurances)->toHaveCount(1)
        ->and($page->itinerary->insurances->first()->id)->toBe('insurance-declined')
        ->and($page->itinerary->insurances->first()->name)->toBe('Customer declined insurance')
        ->and($page->itinerary->insurances->first()->availability)->toBe(AvailabilityEnum::None);
});
