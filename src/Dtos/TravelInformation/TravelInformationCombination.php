<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\TravelInformation;

use Nezasa\Checkout\Integrations\Passolution\Dtos\Responses\PassolutionContentResponse;

class TravelInformationCombination
{
    public function __construct(
        public readonly string $destinationCountryCode,
        public readonly string $nationalityCountryCode,
        public readonly PassolutionContentResponse $content,
    ) {}

    public function title(): string
    {
        return $this->content->title() ?? trans('checkout::page.trip_details.travel_information_combination_title', [
            'destination' => $this->destinationCountryCode,
            'nationality' => $this->nationalityCountryCode,
        ]);
    }
}
