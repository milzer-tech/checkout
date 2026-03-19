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

        if (! $this->canExpand($section)) {
            $blocking = $this->firstIncompleteSection();

            $this->dispatch('toast', [
                'type' => 'error',
                'title' => trans('checkout::page.trip_details.error'),
                'message' => trans('checkout::exceptions.please_complete_this_section').($blocking instanceof \Nezasa\Checkout\Enums\Section ? ': '.$blocking->label() : ''),
            ]);

            return;
        }

        $this->resetLaterSections($section);

        $this->isExpanded = true;

        resolve(SaveSectionStatusAction::class)
            ->run($this->model, $section, $this->isCompleted, $this->isExpanded);
    }

    /**
     * Reopen an already completed section for editing and force the user to continue forward again.
     */
    public function reopen(Section|string $section): void
    {
        $section = $section instanceof Section ? $section : Section::from($section);

        if (! $this->canExpand($section)) {
            $blocking = $this->firstIncompleteSection();

            $this->dispatch('toast', [
                'type' => 'error',
                'title' => trans('checkout::page.trip_details.error'),
                'message' => trans('checkout::exceptions.please_complete_this_section').($blocking instanceof \Nezasa\Checkout\Enums\Section ? ': '.$blocking->label() : ''),
            ]);

            return;
        }

        $this->markAsNotCompletedAndExpand($section);

        $this->resetLaterSections($section);
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
     * Collapse and mark as not completed all sections that come after the given one.
     */
    protected function resetLaterSections(Section $section): void
    {
        $flow = $this->sectionFlow();
        $index = array_search($section, $flow, true);

        if ($index === false) {
            return;
        }

        $laterSections = array_slice($flow, $index + 1);

        if ($laterSections === []) {
            return;
        }

        $updates = [];

        foreach ($laterSections as $later) {
            $updates["status.{$later->value}.isCompleted"] = false;
            $updates["status.{$later->value}.isExpanded"] = false;
        }

        $this->model->updateData($updates);

        $this->dispatch('sections-reset', sections: array_map(fn (Section $s): string => $s->value, $laterSections));
    }

    /**
     * Ordered list of sections to enforce linear progression.
     *
     * @return array<int, Section>
     */
    protected function sectionFlow(): array
    {
        return [
            Section::Contact,
            Section::Traveller,
            Section::Activity,
            Section::Promo,
            Section::AdditionalService,
            Section::Insurance,
            Section::TermsAndConditions,
            Section::PaymentOptions,
        ];
    }

    /**
     * Get the first incomplete section based on stored status.
     */
    protected function firstIncompleteSection(): ?Section
    {
        $status = $this->model->data['status'] ?? [];

        foreach ($this->sectionFlow() as $section) {
            if (! data_get($status, "{$section->value}.isCompleted")) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Determine if the requested section may be expanded.
     */
    protected function canExpand(Section $section): bool
    {
        $flow = $this->sectionFlow();
        $requestedIndex = array_search($section, $flow, true);

        if ($requestedIndex === false) {
            return true;
        }

        $firstIncomplete = $this->firstIncompleteSection();

        if (! $firstIncomplete instanceof \Nezasa\Checkout\Enums\Section) {
            return true;
        }

        $firstIncompleteIndex = array_search($firstIncomplete, $flow, true);

        return $requestedIndex <= $firstIncompleteIndex;
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
