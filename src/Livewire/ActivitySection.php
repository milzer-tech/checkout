<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto as AnswerActivity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerInputEnum;
use Nezasa\Checkout\Jobs\SaveAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\UpdateAnswerActivityQuestionJob;
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
     * //     * @var Collection<int, ActivityQuestionResponse>
     */
    public Collection $activityQuestions;

    /**
     * The result of the activity questions.
     *
     * @var array<string, array<string, string>>
     */
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

        if (! $this->shouldRender) {
            dispatch($verifyAvailability);

            $this->dispatch(Section::Promo->value);

            return;
        }

        $verifyAvailability->handle();

        $this->activityQuestions = NezasaConnector::make()->checkout()->activityQuestions($this->checkoutId)->dto();

        foreach ($this->activityQuestions as $component) {
            foreach ($component->questions as $question) {

                $this->result[$component->componentId][$question->refId] = data_get(
                    $this->model->data,
                    ".$component->componentId.$question->refId"
                );
            }
        }

        dispatch(new UpdateAnswerActivityQuestionJob($this->checkoutId, $this->activityQuestions));
    }

    /**
     * Update the answer to an activity question.
     */
    public function updating(string $property, mixed $value): void
    {
        [$componentId, $questionId] = str($property)->after('.')->explode('.');

        $this->validate(
            rules: [$property => $this->rules()['result'][$componentId][$questionId]],
            attributes: [$property => str($property)->afterLast('.')->toString()]
        );

        dispatch(
            new SaveAnswerActivityQuestionJob($this->checkoutId, new AnswerActivity($componentId, $questionId, $value))
        );
    }

    /**
     * @return array<string, array<string, string|Rule>>
     */
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
