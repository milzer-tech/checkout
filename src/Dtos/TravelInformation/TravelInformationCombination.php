<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\TravelInformation;

use Nezasa\Checkout\Integrations\Passolution\Dtos\Responses\PassolutionRecordResponse;

class TravelInformationCombination
{
    public function __construct(
        public readonly string $destinationCountryCode,
        public readonly string $nationalityCountryCode,
        public readonly ?PassolutionRecordResponse $record = null,
    ) {}

    public function title(): string
    {
        if ($this->record instanceof PassolutionRecordResponse && $this->record->title !== null) {
            return $this->record->title;
        }

        return trans('checkout::page.trip_details.travel_information_combination_title', [
            'destination' => $this->destinationCountryCode,
            'nationality' => $this->nationalityCountryCode,
        ]);
    }

    public function health(): ?string
    {
        return $this->record?->healthContent();
    }

    public function entry(): ?string
    {
        return $this->record?->entryContent();
    }

    public function visa(): ?string
    {
        return $this->record?->visaContent();
    }

    public function transitVisa(): ?string
    {
        return $this->record?->transitVisaContent();
    }
}
