<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Jobs\SaveSectionStatusJob;
use Nezasa\Checkout\Models\Checkout;

class BaseCheckoutComponent extends Component
{
    /**
     * The unique identifier for the checkout process.
     */
    #[Url]
    public string $checkoutId;

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

        SaveSectionStatusJob::dispatch($this->checkoutId, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Collapse the section, hiding it from view.
     */
    public function collapse(Section $section): void
    {
        $this->isExpanded = false;

        SaveSectionStatusJob::dispatch($this->checkoutId, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Mark the section as completed and save the status.
     */
    protected function markAsCompleted(Section $section): void
    {
        $this->isCompleted = true;

        SaveSectionStatusJob::dispatch($this->checkoutId, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Mark the section as not completed and save the status.
     */
    protected function markAsNotCompleted(Section $section): void
    {
        $this->isCompleted = false;

        SaveSectionStatusJob::dispatch($this->checkoutId, $section, $this->isCompleted, $this->isExpanded);
    }
}
