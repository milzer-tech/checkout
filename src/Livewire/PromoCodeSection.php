<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ApplyPromoCodeResponse;

class PromoCodeSection extends BaseCheckoutComponent
{
    /**
     * The promo code entered by the user.
     */
    public ?string $promoCode = null;

    /**
     * Indicates whether the user does not have a promo code.
     */
    public ?bool $notHavePromoCode = null;

    /**
     * The validation rules for the promo code input.
     *
     * @var array<string, string>
     */
    protected array $rules = [
        'promoCode' => 'required|min:1|max:256',
    ];

    /**
     * The prices data transfer object containing promo code information.
     */
    public ApplyPromoCodeResponse $prices;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void
    {
        $this->promoCode = $this->prices->promoCode?->code;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {      /** @phpstan-ignore-next-line */
        return view('checkout::blades.promo-code-section');
    }

    /**
     * Listen for the 'Activity' event to determine if the promo code section should be expanded or completed.
     */
    #[On(Section::Activity->value)]
    public function listen(): void
    {
        $this->isCompleted
            ? $this->dispatch(Section::Promo->value)
            : $this->expand(Section::Promo);
    }

    /**
     * Apply the promo code.
     */
    public function save(): void
    {
        $this->validate();

        $this->deleteCurrentPromoCode();

        $response = NezasaConnector::make()->checkout()->applyPromoCode($this->checkoutId, $this->promoCode);

        if (! $response->ok()) {
            session()->flash('failedPromoCode', $response->array('problems')[0]['detail']);
        } else {
            $this->prices = $response->dto();
            $this->markAsCompleted(Section::Promo);
        }

        $this->dispatchPriceChangedEvent();
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Promo);

        $this->dispatch(Section::Promo->value);
    }

    /**
     * Handle the case where the user does not have a promo code.
     */
    public function noPromoCode(): void
    {
        $this->notHavePromoCode = true;

        $this->next();
    }

    /**
     * Delete the promo code from the checkout process.
     */
    protected function deleteCurrentPromoCode(): void
    {
        NezasaConnector::make()->checkout()->deletePromoCode($this->checkoutId);

        $this->prices->promoCode = null;
        $this->prices->discountedPackagePrice = $this->prices->packagePrice;

        $this->markAsNotCompleted(Section::Promo);
    }

    /**
     * Dispatch the 'price-changed' event with the updated prices.
     */
    protected function dispatchPriceChangedEvent(): void
    {
        $this->dispatch('price-changed', price: $this->prices);
    }
}
