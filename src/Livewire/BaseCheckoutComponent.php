<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Actions\Operation\SaveSectionStatusAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Models\Checkout;

#[Layout('checkout::layouts.layout')]
class BaseCheckoutComponent extends Component
{
    /**
     * The unique identifier for the itinerary
     */
    #[Url]
    public string $itineraryId;

    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

    /**
     * Indicates the request's source from the IBE or the APP.
     * This can help determine if the user is authenticated (APP) or not (IBE).
     */
    #[Url]
    public string $origin;

    /**
     * The ISO 639-1 language code representing the user's language preference for the itinerary.
     */
    #[Url]
    public ?string $lang = null;

    /**
     * Indicates whether the payment is the down payment or the rest payment.
     */
    #[Url(as: 'rest-payment')]
    public bool $restPayment = false;

    /**
     * The unique identifier for the checkout process.
     */
    public Checkout $model;

    /**
     * Indicates whether the section is expanded or collapsed.
     */
    public bool $isExpanded = false;

    /**
     * Indicates whether the section have been completed.
     */
    public bool $isCompleted = false;

    /**
     * Edit the section, allowing the user to enter data.
     */
    public function expand(Section|string $section): void
    {
        $section = $section instanceof Section ? $section : Section::from($section);

        $this->isExpanded = true;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Collapse the section, hiding it from view.
     */
    public function collapse(Section $section): void
    {
        $this->isExpanded = false;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    public function markAsCompletedAdnCollapse(Section $section): void
    {
        $this->isCompleted = true;
        $this->isExpanded = false;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Mark the section as completed and save the status.
     */
    protected function markAsCompleted(Section $section): void
    {
        $this->isCompleted = true;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Mark the section as not completed and save the status.
     */
    protected function markAsNotCompleted(Section $section): void
    {
        $this->isCompleted = false;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Mark the section as not completed and expand.
     */
    protected function markAsNotCompletedAndExpand(Section $section): void
    {
        $this->isCompleted = false;
        $this->isExpanded = true;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Get the query parameters for the component.
     */
    public function getParams(): CheckoutParamsDto
    {
        return new CheckoutParamsDto(
            checkoutId: $this->checkoutId,
            itineraryId: $this->itineraryId,
            origin: $this->origin,
            lang: $this->lang ?? 'en',
            restPayment: $this->restPayment
        );
    }

    /**
     * Get the planner url.
     */
    #[Computed]
    public function nezasaPlannerUrl(): string
    {
        return config('checkout.nezasa.base_url').'/itineraries/'.$this->itineraryId;
    }
}
