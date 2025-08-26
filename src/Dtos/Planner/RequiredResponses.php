<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Planner;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\AddedRentalCarResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountryCodesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AddedUpsellItemResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\GetItineraryResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RetrieveCheckoutResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\TravelerRequirementsResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\UpsellItemsResponse;

class RequiredResponses extends BaseDto
{
    /**
     * @param  Collection<int, AddedUpsellItemResponseEntity>  $addedUpsellItems
     */
    public function __construct(
        public GetItineraryResponse $itinerary,
        public RetrieveCheckoutResponse $checkout,
        public AddedRentalCarResponse $addedRentalCars,
        public TravelerRequirementsResponse $travelerRequirements,
        public UpsellItemsResponse $upsellItems,
        public Collection $addedUpsellItems,
        public CountryCodesResponse $countryCodes,
        public CountriesResponse $countries,
    ) {}
}
