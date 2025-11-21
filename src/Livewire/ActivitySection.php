<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Nezasa\Checkout\Enums\Section;

class ActivitySection extends BaseCheckoutComponent
{
    /**
     * Indicates whether the component should be rendered.
     */
    public bool $shouldRender = false;

    /**
     * Mount the component/
     */
    public function mount(bool $shouldRender = false): void
    {
        $this->shouldRender = false;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('checkout::blades.activity-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Activity);
    }
}
