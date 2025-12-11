<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddOrRemoveUpsellItemsPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\UpsellItemOfferPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\AddedUpsellItemResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\UpsellItemOfferResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\UpsellServiceCategoryResponseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\UpsellItemsResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
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
     * @var Collection<int, AddedUpsellItemResponseEntity>
     */
    public Collection $addedUpsellItems;

    /**
     * The items that are displayed in the additional services section.
     *
     * @var array<string, array<string, int>|string>
     */
    public array $items;

    /**
     * Create a new instance of the component.
     */
    public function mount(): void
    {
        foreach ($this->upsellItemsResponse->offers as $offer) {
            if ($offer->optOutPossible) {
                $this->items[$offer->offerId] = $this->getNoSelectionValue();

                $offer->serviceCategories->push(
                    new UpsellServiceCategoryResponseDto(
                        serviceCategoryRefId: $this->getNoSelectionValue(),
                        name: trans('checkout::page.trip_details.no_need'),
                        priceType: 'FREE',
                        salesPrice: new Price(0, 'euro')
                    )
                );
            }

            // Check if the item has already been added to the checkout
            if ($select = $this->addedUpsellItems->where('productRefId', $offer->offerId)->first()) {
                $this->items[$offer->offerId] = $select->serviceCategoryRefId;
            }
        }
    }

    /**
     * Render the view for the additional services section.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.additional-services-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->validate();

        $this->markAsCompletedAdnCollapse(Section::AdditionalService);

        $this->dispatch(Section::AdditionalService->value);
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

        if ($this->upsellItemsResponse->offers->isEmpty()) {
            $this->next();
        }
    }

    /**
     * Change the selected box for an upsell item.
     */
    public function changeBox(string $offerId, string $categoryId): void
    {
        try {
            $response = NezasaConnector::make()->checkout()->addOrUpdateUpsellItem(
                checkoutId: $this->checkoutId,
                payload: new AddOrRemoveUpsellItemsPayload(
                    selection: new Collection([
                        new UpsellItemOfferPayloadEntity(
                            offerId: $offerId,
                            serviceCategoryRefId: $categoryId === $this->getNoSelectionValue() ? null : $categoryId,
                            quantity: $categoryId === $this->getNoSelectionValue() ? null : 1,
                        ),
                    ])
                )
            );

        } finally {
            $this->dispatch('summary-updated');
            $this->validate(['items.'.$offerId => $this->rules()['items.'.$offerId]]);
        }

    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function rules(): array
    {
        return $this->upsellItemsResponse
            ->offers
            ->mapWithKeys(function (UpsellItemOfferResponseEntity $offer, int $key): array {
                $ids = $offer->serviceCategories
                    ->map(fn (UpsellServiceCategoryResponseDto $category): string => $category->serviceCategoryRefId)
                    ->toArray();

                if ($offer->optOutPossible) {
                    $ids[] = $this->getNoSelectionValue();
                }

                return [
                    'items.'.$offer->offerId => ['required', Rule::in($ids)],
                ];
            })
            ->toArray();
    }

    /**
     * Get the value used to indicate that the user does not want to select a category for an upsell item.
     */
    public function getNoSelectionValue(): string
    {
        return 'no_selection';
    }
}
