<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\VerifyAvailabilityResponse;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;

class VerifyAvailabilityAction
{
    public function run(string $checkoutId, ItinerarySummary $itinerary): bool
    {
        $dto = $this->getVerifyAvailabilityResponse($checkoutId);

        /** @var Collection<int, bool> $statuses */
        $statuses = new Collection;

        foreach ($dto->summary->components as $component) {
            $statuses->add($component->nonBookable);

            if ($component->isPlaceholder) {
                continue;
            }

            $item = match ($component->componentType) {
                ComponentEnum::Accommodation => $itinerary->stays->firstWhere('id', $component->id),
                ComponentEnum::Activity => $itinerary->activities->firstWhere('id', $component->id),
                ComponentEnum::Flight => $itinerary->flights->firstWhere('id', $component->id),
                ComponentEnum::RentalCar => $itinerary->rentalCars->firstWhere('id', $component->id),
                ComponentEnum::Transfer => $itinerary->transfers->firstWhere('id', $component->id),
                ComponentEnum::UpsellItem => $itinerary->upsellItems->firstWhere('id', $component->id),
                default => null,
            };

            if ($item) {
                $item->availability = $component->status;
            }
        }

        return $statuses->reject(fn (bool $item): bool => ! $item)->isEmpty();
    }

    /**
     * Get the availability response from the cache or Nezasa API.
     */
    protected function getVerifyAvailabilityResponse(string $checkoutId): VerifyAvailabilityResponse
    {
        if ((int) Cache::get('varifyAvailability-status-'.$checkoutId, 500) === 200) {
            $dto = VerifyAvailabilityResponse::from(Cache::get('varifyAvailability-'.$checkoutId));
        } else {
            /** @var VerifyAvailabilityResponse $dto */
            $dto = NezasaConnector::make()->checkout()->varifyAvailability($checkoutId)->dto();
        }

        return $dto;
    }
}
