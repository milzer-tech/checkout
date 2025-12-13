<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TermsAndConditionsResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\TextSectionResponseEntity;
use Nezasa\Checkout\Jobs\SaveTermAgreementJob;

class TermsSection extends BaseCheckoutComponent
{
    /**
     * The terms and conditions response containing the terms and conditions.
     */
    public TermsAndConditionsResponseEntity $termsAndConditions;

    /**
     * The accepted terms and conditions.
     *
     * @var array<string, bool>
     */
    public array $acceptedTerms = [];

    /**
     * Whether to show the terms modal.
     */
    public bool $showTermsModal = false;

    /**
     * The index of the currently displayed modal term.
     */
    public ?int $modalTermIndex = null;

    /**
     * Initialize the component with the promo code from the prices DTO.
     */
    public function mount(): void
    {
        foreach ($this->model->data['acceptedTerms'] as $key => $value) {
            if ($value) {
                $this->acceptedTerms[$key] = $value;
            }
        }
    }

    /**
     * Get the validation rules that apply to the component's inputs.'
     *
     * @return array<string, array<string, string>>
     */
    protected function rules(): array
    {
        return $this->termsAndConditions->sections
            ->filter(fn (TextSectionResponseEntity $section): bool => $section->checkboxText !== null)
            ->mapWithKeys(fn (TextSectionResponseEntity $value, $key): array => [
                'acceptedTerms.'.$value->getKey() => ['required', 'accepted'],
            ])
            ->toArray();
    }

    /**
     * Listen for the input changes and save the promo code in the checkout DTO.
     */
    public function toggleBox(string $name, bool $value): void
    {
        dispatch(new SaveTermAgreementJob($this->checkoutId, $name, $value));

        $this->validate([$name => $this->rules()[$name]]);
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.terms-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        if ($this->termsAndConditions->sections->isNotEmpty()) {
            $this->validate($this->rules());
        }

        $this->markAsCompletedAdnCollapse(Section::TermsAndConditions);

        $this->dispatch(Section::TermsAndConditions->value);
    }

    /**
     * Listen for the additional service section, expanding the terms and conditions section.
     */
    #[On(Section::Insurance->value)]
    public function listen(): void
    {
        $this->termsAndConditions->sections->isEmpty()
            ? $this->next()
            : $this->expand(Section::TermsAndConditions);
    }

    /**
     * Show the terms modal.
     */
    public function openTermsModal(int $index): void
    {
        $this->modalTermIndex = $index;

        $this->showTermsModal = true;
    }

    /**
     * Hide the terms modal.
     */
    public function closeTermsModal(): void
    {
        $this->showTermsModal = false;

        $this->modalTermIndex = null;
    }
}
