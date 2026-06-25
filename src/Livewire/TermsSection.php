<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceTerms;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\EuPrrlResponseEntity;
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
     * EU-PRRL terms and compliance information.
     */
    public ?EuPrrlResponseEntity $euPrrl = null;

    /**
     * The accepted terms and conditions.
     *
     * @var array<string, bool>
     */
    public array $acceptedTerms = [];

    /**
     * Whether the EU-PRRL general terms were accepted.
     */
    public bool $acceptedEuPrrlTerms = false;

    /**
     * Whether to show the terms modal.
     */
    public bool $showTermsModal = false;

    /**
     * The index of the currently displayed modal term.
     */
    public ?int $modalTermIndex = null;

    public ?InsuranceTerms $insuranceTerms = null;

    /**
     * @var array<string, bool>
     */
    public $acceptedInsurance = [];

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

        if ($this->requiresEuPrrlGeneralTermsConfirmation()) {
            $this->acceptedEuPrrlTerms = (bool) data_get(
                target: $this->model->data,
                key: 'acceptedTerms.'.$this->euPrrl?->getGeneralTermsKey(),
                default: false
            );
        }
    }

    /**
     * Get the validation rules that apply to the component's inputs.'
     *
     * @return array<string, array<string, string>>
     */
    protected function rules(): array
    {
        $rules = $this->termsAndConditions->sections
            ->filter(fn (TextSectionResponseEntity $section): bool => $section->checkboxText !== null)
            ->mapWithKeys(fn (TextSectionResponseEntity $value, $key): array => [
                'acceptedTerms.'.$value->getKey() => ['required', 'accepted'],
            ])
            ->toArray();

        if ($this->insuranceTerms?->checkboxText) {
            $insurances = ['acceptedInsurance.'.$this->insuranceTerms->getKey() => ['required', 'accepted']];
        }

        if ($this->requiresEuPrrlGeneralTermsConfirmation()) {
            $euPrrlTerms = ['acceptedEuPrrlTerms' => ['required', 'accepted']];
        }

        return array_merge($rules, $insurances ?? [], $euPrrlTerms ?? []);
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
     * Validate the EU-PRRL checkbox and clear its inline error once accepted.
     */
    public function toggleEuPrrlTerms(bool $value): void
    {
        $this->acceptedEuPrrlTerms = $value;

        if ($this->euPrrl instanceof EuPrrlResponseEntity) {
            $this->model->updateData([
                'acceptedTerms.'.$this->euPrrl->getGeneralTermsKey() => $value,
            ]);
        }

        if ($value) {
            $this->resetValidation('acceptedEuPrrlTerms');

            return;
        }

        $this->validateOnly('acceptedEuPrrlTerms');
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
        $rules = $this->rules();

        if ($rules !== []) {
            $this->validate($rules);
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
        $this->insuranceTerms = null;
        $offer = InsuranceCheckoutData::getOffer(InsuranceCheckoutData::checkoutDataArray($this->model->data));
        $insuranceTerms = $offer ? InsuranceOfferDto::from($offer)->terms : null;

        if ($insuranceTerms instanceof InsuranceTerms && $this->hasInsuranceTermsContent($insuranceTerms)) {
            $this->insuranceTerms = $insuranceTerms;
        }

        ($this->termsAndConditions->sections->isEmpty()
            && ! $this->insuranceTerms instanceof InsuranceTerms
            && ! $this->requiresEuPrrlGeneralTermsConfirmation())
            ? $this->next()
            : $this->expand(Section::TermsAndConditions);
    }

    public function requiresEuPrrlGeneralTermsConfirmation(): bool
    {
        return $this->euPrrl?->generalTermsConfirmationEnabled === true;
    }

    private function hasInsuranceTermsContent(InsuranceTerms $terms): bool
    {
        return $terms->text !== null
            || $terms->checkboxText !== null
            || $terms->conditions !== [];
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

    /**
     * Reset the section.
     *
     * @param  array<int, string>  $sections
     */
    #[On('sections-reset')]
    public function resetSection(array $sections): void
    {
        if (! in_array(Section::TermsAndConditions->value, $sections, true)) {
            return;
        }

        $this->isCompleted = false;
        $this->isExpanded = false;
    }
}
