<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Supporters\InsuranceSupporter;

class InsuranceSection extends BaseCheckoutComponent
{
    /**
     * The summary of the itinerary.
     */
    public ItinerarySummary $itinerary;

    /**
     * The contact information payload entity.
     */
    public ?ContactInfoPayloadEntity $contact = null;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void
    {
        if (isset($this->model->data['contact'])) {
            $this->contact = ContactInfoPayloadEntity::from($this->model->data['contact']);
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.insurance-section');
    }

    /**
     * Handle the insurance quote.
     *
     * @param  array<string, mixed>|null  $quote
     */
    public function handleInsuranceQuote(?array $quote): void
    {
        $this->model->updateData(['insurance' => $quote]);

        if (is_null($quote)) {
            $this->dispatch('insurance-declined');

            return;
        }

        $this->dispatch(
            'insurance-selected',
            new InsuranceItem(id: $quote['quote_id'], name: $quote['product']['promotional_header']),
            new Price(amount: $quote['total'] / 100, currency: $quote['currency'])
        );
    }

    /**
     * Listen for the additional service section, expanding the insurance section.
     */
    #[On(Section::AdditionalService->value)]
    public function listen(): void
    {
        if (InsuranceSupporter::isAvailable()) {
            $this->expand(Section::Insurance);
        } else {
            $this->next();
        }
    }

    /**
     * Update the price.
     *
     * @param  array<string, array<string, float>>  $price
     */
    #[On('price-updated')]
    public function priceUpdated(array $price): void
    {
        if (! InsuranceSupporter::isAvailable()) {
            return;
        }

        $this->itinerary->price = ApplyPromoCodeResponse::from($price);

        // tell JS side to refresh insurance widget with the new config
        $this->dispatch('insurance-config-updated', config: $this->verticalInsuranceConfig);
    }

    /**
     * Go to the next section.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Insurance);

        $this->dispatch(Section::Insurance->value);
    }

    /**
     *  Get the vertical insurance config.
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function getVerticalInsuranceConfigProperty(): array
    {
        if (! InsuranceSupporter::isAvailable() || ! $this->contact) {
            return [];
        }

        return [
            'client_id' => config()->string('checkout.insurance.vertical.username'),
            'product_config' => [
                'travel' => [[
                    'customer' => [
                        'first_name' => $this->contact->firstName,
                        'last_name' => $this->contact->lastName,
                        'email_address' => $this->contact->email,
                        'street' => $this->contact->address->street1.' '.$this->contact->address->street2,
                        'city' => $this->contact->address->city,
                        'postal_code' => $this->contact->address->postalCode,
                        'country' => str($this->contact->address->country)->beforeLast('-')->toString(),
                    ],
                    'attributes' => [
                        'trip_start_date' => $this->itinerary->startDate->toDateString(),
                        'trip_end_date' => $this->itinerary->endDate->toDateString(),
                        'destination_countries' => $this->itinerary->destinationCountries,
                        'trip_cost' => $this->itinerary->price->discountedPackagePrice->toCent(),
                        'trip_cost_currency' => (string) $this->itinerary->price->discountedPackagePrice->currency,
                    ],
                    'currency' => (string) $this->itinerary->price->discountedPackagePrice->currency,
                ]],
            ],
        ];
    }
}
