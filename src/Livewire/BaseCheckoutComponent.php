<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Jobs\SaveSectionStatusJob;
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

        dispatch(
            new SaveSectionStatusJob($this->checkoutId, $section, $this->isCompleted, $this->isExpanded)
        );
    }

    /**
     * Collapse the section, hiding it from view.
     */
    public function collapse(Section $section): void
    {
        $this->isExpanded = false;

        dispatch(
            new SaveSectionStatusJob($this->checkoutId, $section, $this->isCompleted, $this->isExpanded)
        );
    }

    public function markAsCompletedAdnCollapse(Section $section): void
    {
        $this->isCompleted = true;
        $this->isExpanded = false;

        dispatch(
            new SaveSectionStatusJob($this->checkoutId, $section, $this->isCompleted, $this->isExpanded)
        );
    }

    /**
     * Mark the section as completed and save the status.
     */
    protected function markAsCompleted(Section $section): void
    {
        $this->isCompleted = true;

        dispatch(
            new SaveSectionStatusJob($this->checkoutId, $section, $this->isCompleted, $this->isExpanded)
        );
    }

    /**
     * Mark the section as not completed and save the status.
     */
    protected function markAsNotCompleted(Section $section): void
    {
        $this->isCompleted = false;

        dispatch(
            new SaveSectionStatusJob($this->checkoutId, $section, $this->isCompleted, $this->isExpanded)
        );
    }

    /**
     * Get the query parameters for the component.
     *
     * @return array<string, string>
     */
    protected function getQueryParams(): array
    {
        return [
            'checkoutId' => $this->checkoutId,
            'itineraryId' => $this->itineraryId,
            'origin' => $this->origin,
            'lang' => $this->lang,
        ];
    }
}
