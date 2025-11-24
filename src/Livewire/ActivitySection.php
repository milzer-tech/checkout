<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerInputEnum;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;

class ActivitySection extends BaseCheckoutComponent
{
    /**
     * Indicates whether the component should be rendered.
     */
    public bool $shouldRender = false;

    /**
     * The list of activity questions.
     *
     * @var Collection<int, ActivityQuestionResponse>
     */
    public Collection $activityQuestions;

    public array $result = [];

    /**
     * Mount the component/
     */
    public function mount(bool $shouldRender = false): void
    {
        $this->shouldRender = $shouldRender;

        $this->activityQuestions = new Collection;

        if (
            $this->model->data['status']['traveller']['isCompleted']
            && ! $this->model->data['status']['activity']['isCompleted']
        ) {
            $this->listen();
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view($this->shouldRender ? 'checkout::blades.activity-section' : 'checkout::blades.empty');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Activity);
    }

    #[On(Section::Activity->value)]
    public function listen(): void
    {
        $verifyAvailability = new VerifyAvailabilityJob($this->checkoutId);

        if ($this->shouldRender) {
            $verifyAvailability->handle();

            $this->activityQuestions = NezasaConnector::make()
                ->checkout()
                ->getActivityQuestions($this->checkoutId)
                ->dto();

            foreach ($this->activityQuestions as $component) {
                foreach ($component->questions as $question) {
                    $this->result[$component->componentId][$question->refId] = null;
                }
            }
        } else {
            dispatch($verifyAvailability);

            $this->dispatch(Section::Promo->value);
        }
    }

    public function updated($property, $value): void
    {
        [$componentId, $questionId] = str($property)->after('.')->explode('.');

        $this->validate(
            rules: [
                $property => $this->rules()['result'][$componentId][$questionId],
            ],
            attributes: [
                $property => str($property)->afterLast('.')->toString(),
            ]
        );
    }

    protected function rules(): array
    {
        $rules = [];

        foreach ($this->activityQuestions as $component) {
            foreach ($component->questions as $question) {
                $propertyRules = $question->required ? ['required'] : ['nullable'];

                switch ($question->getInputType()) {
                    case AnswerInputEnum::Select:
                        $propertyRules[] = Rule::in($question->answerOptions->pluck('refId')->flatten());
                        break;

                    case AnswerInputEnum::Radio:
                        $propertyRules[] = 'boolean';
                        break;
                    default:
                        $propertyRules[] = 'sometimes';
                }

                $rules['result'][$component->componentId][$question->refId] = $propertyRules;
            }
        }

        return $rules;
    }
}
