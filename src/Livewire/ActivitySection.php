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
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\QuestionResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerValidationEnum;
use Nezasa\Checkout\Jobs\SaveAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\SendActivityAnswersToNezasaJob;
use Nezasa\Checkout\Jobs\UpdateAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;

class ActivitySection extends BaseCheckoutComponent
{
    /**
     * Indicates whether the component should be rendered.
     */
    public bool $shouldRender = false;

    /**
     * The current activity being processed.
     */
    public int $currentActivity = 0;

    /**
     * The list of activity questions.
     *
     * @var Collection<int, ActivityQuestionResponse>
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
    public function mount(): void
    {
        $this->activityQuestions = new Collection;

        if ($this->shouldRender && $this->model->isCompleted(Section::Traveller)) {
            $this->listen();
        }
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        /** @phpstan-ignore-next-line */
        return view('checkout::blades.activity-section');
    }

    /**
     * Edit the promo code section, allowing the user to enter a new promo code.
     */
    public function next(): void
    {
        $this->markAsCompletedAdnCollapse(Section::Activity);

        dispatch(new SendActivityAnswersToNezasaJob($this->checkoutId));

        $this->dispatch(Section::Activity->value);
    }

    /**
     * Listen for finishing the traveler section.
     */
    #[On(Section::Traveller->value)]
    public function listen(): void
    {
        $verifyAvailability = new VerifyAvailabilityJob($this->getParams());

        if (! $this->shouldRender) {
            dispatch($verifyAvailability);
            $this->markAsCompletedAdnCollapse(Section::Activity);
            $this->dispatch(Section::Activity->value);

            return;
        }

        $verifyAvailability->handle();
        $this->activityQuestions = NezasaConnector::make()->checkout()->activityQuestions($this->checkoutId)->dto();

        if ($this->activityQuestions->isEmpty()) {
            $this->shouldRender = false;

            $this->next();
        }

        $this->fillResult();
    }

    /**
     * Update the answer to an activity question.
     */
    public function updated(string $property, mixed $value): void
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
     *  The data validation rules for the component.
     *
     * @return array<string, array<string, array<string, array<int, Rule|string>>>>
     */
    protected function rules(): array
    {
        $rules = [];

        foreach ($this->activityQuestions as $component) {
            foreach ($component->questions as $question) {
                $rules['result'][$component->componentId][$question->refId] = $this->answerValidationRules($question);
            }
        }

        return $rules;
    }

    /**
     * Go to the next activity.
     */
    public function nextActivity(int $index): void
    {
        $componentId = $this->activityQuestions->get($index)->componentId;
        $componentRules = $this->rules()['result'][$componentId];

        $activityRules = [];
        $attributes = [];

        foreach ($componentRules as $key => $rule) {
            $activityRules["result.$componentId.$key"] = $rule;
            $attributes["result.$componentId.$key"] = $key;
        }

        $this->validate(rules: $activityRules, attributes: $attributes);

        if ($this->activityQuestions->has($index + 1)) {
            $this->currentActivity = $index + 1;
        } else {
            $this->next();
        }
    }

    /**
     * Go back to the previous activity.
     */
    public function previousActivity(int $index): void
    {
        if ($this->activityQuestions->has($index - 1)) {
            $this->currentActivity = $index - 1;
        }
    }

    /**
     * Get the answer validation rules for a given question.
     *
     *
     * @return array<int, string|Rule>
     */
    protected function answerValidationRules(QuestionResponseEntity $question): array
    {
        $rules = $question->required ? ['required'] : ['nullable'];

        if ($question->getInputType()->isSelect()) {
            $rules[] = Rule::in($question->answerOptions->pluck('refId')->flatten());
        } else {
            $rules[] = match ($question->answerValidation) {
                AnswerValidationEnum::Int => 'integer',
                AnswerValidationEnum::Double => 'float',
                AnswerValidationEnum::Boolean => 'boolean',
                default => 'sometimes',
            };
        }

        return $rules;
    }

    /**
     * Compile the result of the activity questions.
     */
    public function fillResult(): void
    {
        foreach ($this->activityQuestions as $component) {
            foreach ($component->questions as $question) {
                $answer = $this->model->getAnswer($component->componentId, $question->refId);

                if (is_null($answer) && $question->required) {
                    $this->markAsNotCompletedAndExpand(Section::Activity);
                }

                $this->result[$component->componentId][$question->refId] = $answer;
            }
        }

        dispatch(new UpdateAnswerActivityQuestionJob($this->checkoutId, $this->activityQuestions));
    }
}
