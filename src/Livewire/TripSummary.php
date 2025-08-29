<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\VerifyAvailabilityResponse;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;
use Nezasa\Checkout\Models\Checkout;

class TripSummary extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * The unique identifier for the itinerary.
     */
    public function mount(bool $travellerProcessed = false): void {}

    /**
     * Render the component view.
     */
    public function render(): View
    {   /** @phpstan-ignore-next-line */
        return view('checkout::blades.trip-summary');
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $prices = ApplyPromoCodeResponse::from($prices);

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;

        $this->itinerary->promoCodeResponse = $prices;
    }

    /**
     * Handle the summary updated event.
     */
    #[On('summary-updated')]
    public function summaryUpdated(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $summary = Checkout::whereCheckoutId($this->itineraryId)->value('data')->get('status')['summary'];

            if ($summary['isCompleted']) {
                break;
            }

            sleep(1);
        }

        $prices = NezasaConnector::make()->checkout()->retrieve($this->itineraryId)->dto()->prices;

        $this->itinerary->price = $prices->discountedPackagePrice ?? $prices->packagePrice;
    }

    /**
     * Handle the promo code applied event.
     */
    #[On('payment-selected')]
    public function verifyAvailability(): void
    {
        if ((int) Cache::get('varifyAvailability-status-'.$this->checkoutId, 500) === 200) {
            $dto = VerifyAvailabilityResponse::from(Cache::get('varifyAvailability-'.$this->checkoutId));
        } else {
            $dto = NezasaConnector::make()->checkout()->varifyAvailability($this->checkoutId)->dto();
        }

        /** @var Collection<int, AvailabilityEnum> $statuses */
        $statuses = new Collection;

        foreach ($dto->summary->components as $component) {
            if ($component->isPlaceholder) {
                continue;
            }

            $item = match ($component->componentType) {
                ComponentEnum::Accommodation => $this->itinerary->stays->firstWhere('id', $component->id),
                ComponentEnum::Activity => $this->itinerary->activities->firstWhere('id', $component->id),
                ComponentEnum::Flight => $this->itinerary->flights->firstWhere('id', $component->id),
                ComponentEnum::RentalCar => $this->itinerary->rentalCars->firstWhere('id', $component->id),
                ComponentEnum::Transfer => $this->itinerary->transfers->firstWhere('id', $component->id),
                ComponentEnum::UpsellItem => $this->itinerary->upsellItems->firstWhere('id', $component->id),
                default => null,
            };

            if ($item) {
                $item->availability = $component->status;

                $statuses->add($item->availability);
            }
        }

        $availability = $statuses->reject(fn (AvailabilityEnum $item): bool => $item->isBookable())->isEmpty();

        $this->dispatch('availability-verified', result: $availability);
    }
}
