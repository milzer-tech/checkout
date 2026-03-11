<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Providers\HanseMerkur;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Integrations\HanseMerkur\Connectors\HanseMerkurConnector;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCoveredEventPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurInsuredPersonPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMoneyEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateOffersPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities\HanseMerkurOfferProductResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

final class HanseMerkurInsurance implements InsuranceContract
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.insurance.hanse_merkur.active');
    }

    /**
     * {@inheritDoc}
     */
    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult
    {
        $response = HanseMerkurConnector::make()->offers()->create(
            new HanseMerkurCreateOffersPayload(
                coveredEvent: new HanseMerkurCoveredEventPayloadEntity(
                    bookingConfirmationDate: now()->toImmutable(),
                    eventStartDate: $createOffersDto->startDate,
                    eventEndDate: $createOffersDto->endDate,
                    totalEventCost: HanseMerkurMoneyEntity::from($createOffersDto->totalPrice),
                    destinationCountries: $createOffersDto->destinationCountries->toArray(),
                ),
                insuredPersons: $createOffersDto->paxInfo->mapWithKeys(fn (PaxInfoPayloadEntity $person, int $key) => [
                    new HanseMerkurInsuredPersonPayloadEntity(
                        insuredPersonId: $key + 1,
                        birthDate: now()->toImmutable(),
                    ),
                ]),
            )
        );

        $offers = [];

        /** @var HanseMerkurOfferProductResponseEntity $product */
        foreach ($response->dto()->offers->pluck('products')->flatten()->sortBy('productTotalPremium.amount') as $product) {
            $offers[] = new InsuranceOfferDto(
                id: $product->productInstanceId,
                title: $product->title ?? 'Unknown',
                price: Price::from($product->productTotalPremium),
                coverage: $product->coverageData->pluck('title')->toArray()
            );
        }

        return new InsuranceOffersResult(isSuccessful: $response->ok(), offers: $offers, meta: $response->array());
    }
}
