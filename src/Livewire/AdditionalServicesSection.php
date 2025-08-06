<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AddedUpsellItemResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\UpsellItemsResponse;
use Nezasa\Checkout\Jobs\AddOrUpdateUpsellItemJob;
use Nezasa\Checkout\Jobs\SaveSectionStatusJob;
use Nezasa\Checkout\Models\Checkout;

class AdditionalServicesSection extends BaseCheckoutComponent
{
    /**
     * The upsell items response containing available upsell items.
     */
    public UpsellItemsResponse $upsellItemsResponse;

    /**
     * @var array<int, AddedUpsellItemResponseEntity>
     */
    public array $addedUpsellItems;

    public array $items = [];

    public array $adHocItems = [];

    public function mount(): void
    {
        foreach ($this->upsellItemsResponse->offers as $offer) {
            foreach ($offer->serviceCategories as $service) {
                $this->items[$offer->offerId][$service->serviceCategoryRefId] = collect($this->addedUpsellItems)
                    ->where('productRefId', $offer->offerId)
                    ->where('serviceCategoryRefId', $service->serviceCategoryRefId)
                    ->count();
            }
        }
    }

    /**
     * Render the view for the additional services section.
     */
    public function render(): View
    {
        return view('checkout::trip-details-page.additional-services-section');
    }

    public function addItem(bool $isAdHoc, string $offerId, string $serviceCategoryRefId): void
    {
        if ($isAdHoc) {

        } else {
            $this->items[$offerId][$serviceCategoryRefId]++;

            $this->updateUpsellItems($offerId, $serviceCategoryRefId);
        }
    }

    public function removeItem(bool $isAdHoc, string $offerId, string $serviceCategoryRefId): void
    {
        if ($isAdHoc) {
        } else {
            $this->items[$offerId][$serviceCategoryRefId] == 0 ?: $this->items[$offerId][$serviceCategoryRefId]--;

            $this->updateUpsellItems($offerId, $serviceCategoryRefId);
        }
    }

    public function noNeed(bool $isAdHoc, string $offerId): void
    {
        if ($isAdHoc) {
        } else {
            foreach ($this->items[$offerId] as $serviceId => $quantity) {
                if ($quantity !== 0) {
                    $this->items[$offerId][$serviceId] = 0;

                    $this->updateUpsellItems($offerId, $serviceId);
                }
            }
        }
    }

    /**
     * Update the upsell items in the checkout.
     */
    protected function updateUpsellItems(string $offerId, string $serviceCategoryRefId): void
    {
        $quantity = $this->items[$offerId][$serviceCategoryRefId];

        foreach ($this->items[$offerId] as $serId => $service) {
            if ($serId === $serviceCategoryRefId) {
                continue;
            }

            $this->items[$offerId][$serId] = 0;
        }

        SaveSectionStatusJob::make($this->checkoutId, Section::Summary, false)->handle();

        AddOrUpdateUpsellItemJob::dispatch($this->checkoutId, $offerId, $serviceCategoryRefId, $quantity);

        $this->dispatch('summary-updated');
    }
}
