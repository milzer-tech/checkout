<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Dtos\Planner\Entities\InsuranceItem;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
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
     */
    public function handleInsuranceQuote(?array $quote)
    {
        $this->model->updateData(['insurance' => $quote]);

        if (is_null($quote)) {
            $this->dispatch('insurance-declined');

            return;
        }

        $this->dispatch(
            'insurance-selected',
            new InsuranceItem(id: $quote['quote_id'], name: $quote['product']['promotional_header']),
            new Price(amount: intval($quote['total'] / 100), currency: $quote['currency'])
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
     * Go to the next section.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Insurance);

        $this->dispatch(Section::Insurance->value);
    }
}
