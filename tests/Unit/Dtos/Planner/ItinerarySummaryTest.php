<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryStay;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;

function buildPriceSet(): ApplyPromoCodeResponse {
    return new ApplyPromoCodeResponse(
        discountedPackagePrice: new Price(1000, 'CHF'),
        packagePrice: new Price(1200, 'CHF'),
        totalPackagePrice: new Price(1200, 'CHF'),
        downPayment: new Price(200, 'CHF'),
        externallyPaidCharges: new ExternallyPaidChargesResponseEntity(totalPrice: new Price(0, 'CHF')),
    );
}

it('computes group availability statuses when items share the same status', function (): void {
    $price = buildPriceSet();
    $start = CarbonImmutable::parse('2024-06-01');
    $stayStatus = AvailabilityEnum::Available;

    $summary = new ItinerarySummary(
        price: $price,
        title: 'Trip',
        startDate: $start,
        endDate: $start->addDays(3),
        adults: 2,
        stays: new Collection([
            new ItineraryStay('s1', 'Stay 1', $start, 1, $stayStatus),
            new ItineraryStay('s2', 'Stay 2', $start->addDay(), 2, $stayStatus),
        ]),
        termsAndConditions: new TermsAndConditionsResponseEntity,
    );

    expect($summary->getHotelsGroupStatus())->toBe($stayStatus);
});

it('returns null group status when availability differs', function (): void {
    $price = buildPriceSet();
    $start = CarbonImmutable::parse('2024-06-01');

    $summary = new ItinerarySummary(
        price: $price,
        title: 'Trip',
        startDate: $start,
        endDate: $start->addDays(2),
        adults: 2,
        stays: new Collection([
            new ItineraryStay('s1', 'Stay 1', $start, 1, AvailabilityEnum::Available),
            new ItineraryStay('s2', 'Stay 2', $start->addDay(), 1, AvailabilityEnum::OnRequest),
        ]),
        termsAndConditions: new TermsAndConditionsResponseEntity,
    );

    expect($summary->getHotelsGroupStatus())->toBeNull();
});
