<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\TravelInformation;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\TravelInformation\TravelInformationCombination;
use Nezasa\Checkout\Integrations\Passolution\Connectors\PassolutionConnector;
use Nezasa\Checkout\Integrations\Passolution\Dtos\Responses\PassolutionContentResponse;
use Nezasa\Checkout\Integrations\Passolution\Requests\GetContentRequest;
use Nezasa\Checkout\Models\Checkout;
use Throwable;

class LoadTravelInformationAction
{
    /**
     * @param  Collection<int, string>|array<int, string>  $destinationCountries
     * @return Collection<int, TravelInformationCombination>
     */
    public function run(Checkout $checkout, Collection|array $destinationCountries, string $language): Collection
    {
        $destinations = collect($destinationCountries)
            ->filter(fn (mixed $country): bool => is_string($country) && trim($country) !== '')
            ->map(fn (string $country): string => $this->normalizeCountryCode($country))
            ->unique()
            ->values();

        $nationalities = $checkout->getPaxInfo()
            ->map(fn ($pax): ?string => $pax->nationalityCountryCode ?: $pax->nationality)
            ->filter(fn (mixed $country): bool => is_string($country) && trim($country) !== '')
            ->map(fn (string $country): string => $this->normalizeCountryCode($country))
            ->unique()
            ->values();

        if ($destinations->isEmpty() || $nationalities->isEmpty()) {
            return new Collection;
        }

        $content = $this->loadContent($destinations, $nationalities, $language);

        return $destinations
            ->crossJoin($nationalities)
            ->map(fn (array $pair): TravelInformationCombination => new TravelInformationCombination(
                destinationCountryCode: $pair[0],
                nationalityCountryCode: $pair[1],
                record: $content->recordForCombination($pair[0], $pair[1]),
            ));
    }

    /**
     * @param  Collection<int, string>  $destinationCountryCodes
     * @param  Collection<int, string>  $nationalityCountryCodes
     */
    private function loadContent(Collection $destinationCountryCodes, Collection $nationalityCountryCodes, string $language): PassolutionContentResponse
    {
        try {
            return PassolutionConnector::make()
                ->send(new GetContentRequest($destinationCountryCodes, $nationalityCountryCodes, $language))
                ->dto();
        } catch (Throwable $throwable) {
            report($throwable);

            return PassolutionContentResponse::fromPayload([]);
        }
    }

    private function normalizeCountryCode(string $country): string
    {
        return str($country)
            ->trim()
            ->before('-')
            ->upper()
            ->toString();
    }
}
