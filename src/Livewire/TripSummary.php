<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\VerifyAvailabilityResponse;
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
    public function mount(bool $travellerProcessed = false): void
    {
        if ($travellerProcessed) {
            $this->verifyAvailability();
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.trip-summary');
    }

    /**
     * Handle the promo code applied event.
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('price-changed')]
    public function priceChanged(array $prices): void
    {
        $promoCodeResponse = ApplyPromoCodeResponse::from($prices);

        $this->itinerary->price = $promoCodeResponse->discountedPackagePrice;

        $this->itinerary->promoCodeResponse = $promoCodeResponse;
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
     *
     * @param  array<string, array<string, float>>  $prices
     */
    #[On('traveller-processed')]
    public function verifyAvailability(): void
    {
        /** @var VerifyAvailabilityResponse $dto */
        $dto = NezasaConnector::make()->checkout()->varifyAvailability($this->checkoutId)->dto();

        foreach ($dto->summary->components as $component) {
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
            }
        }
    }
}
