<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
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
     * The items that have been added to the checkout.
     *
     * @var array<int, AddedUpsellItemResponseEntity>
     */
    public array|Collection $addedUpsellItems;

    /**
     * The items that are displayed in the additional services section.
     *
     * @var array<string, array<string, int>>
     */
    public array $items = [];

    /**
     * The ad-hoc items that are displayed in the additional services section.
     *
     * @var array<int, AddedUpsellItemResponseEntity>
     */
    public array $adHocItems = [];

    /**
     * Create a new instance of the component.
     */
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
    {   /** @phpstan-ignore-next-line  */
        return view('checkout::trip-details-page.additional-services-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::AdditionalService);

        $this->dispatch(Section::AdditionalService->value);
    }

    /**
     * Add an item to the checkout.
     */
    public function addItem(bool $isAdHoc, string $offerId, string $serviceCategoryRefId): void
    {
        if ($isAdHoc) {

        } else {
            $this->items[$offerId][$serviceCategoryRefId]++;

            $this->updateUpsellItems($offerId, $serviceCategoryRefId);
        }
    }

    /**
     * Remove an item from the checkout.
     */
    public function removeItem(bool $isAdHoc, string $offerId, string $serviceCategoryRefId): void
    {
        if ($isAdHoc) {
        } else {
            $this->items[$offerId][$serviceCategoryRefId] == 0 ?: $this->items[$offerId][$serviceCategoryRefId]--;

            $this->updateUpsellItems($offerId, $serviceCategoryRefId);
        }
    }

    /**
     * Make all ids of an offer zero.
     */
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

    /**
     * Listen for the 'traveller-processed' event to determine if the promo code section should be expanded or completed.
     */
    #[On(Section::Promo->value)]
    public function listen(): void
    {
        $this->isCompleted
            ? $this->next()
            : $this->expand(Section::AdditionalService);
    }
}
