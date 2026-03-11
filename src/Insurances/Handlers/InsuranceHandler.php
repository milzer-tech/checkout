<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Handlers;

use Exception;
use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Models\Checkout;

final class InsuranceHandler
{
    public function __construct(private GetActiveInsuranceAction $getActiveInsuranceAction) {}

    /**
     * Indicate if any insurance provider is active.
     *
     * @throws Exception
     */
    public function isAvailable(): bool
    {
        return $this->getActiveInsuranceAction->run() instanceof InsuranceContract;
    }

    /**
     * @return false|array<int, InsuranceOfferDto>
     */
    public function createOffers(Checkout $model, ItinerarySummary $itinerary): false|array
    {
        $createOffersDto = new CreateInsuranceOffersDto(
            startDate: $itinerary->startDate->toImmutable(),
            endDate: $itinerary->endDate->toImmutable(),
            totalPrice: $itinerary->price->showTotalPrice,
            contact: $model->getContact(),
            paxInfo: $model->getPaxInfo(),
            destinationCountries: $itinerary->destinationCountries,
        );

        $result = $this->getActiveInsuranceAction->run()->getOffers($createOffersDto);

        $model->updateData(['insurance_meta' => $result->meta]);

        return $result->isSuccessful ? $result->offers : false;
    }
}
