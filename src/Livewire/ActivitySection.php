<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Exceptions\UnavailableServiceException;
use Nezasa\Checkout\Facades\AvailabilityFacade;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto as AnswerActivity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\QuestionResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerValidationEnum;
use Nezasa\Checkout\Jobs\SaveAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\SendActivityAnswersToNezasaJob;
use Nezasa\Checkout\Jobs\UpdateAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;
use RuntimeException;
use Throwable;

class ActivitySection extends BaseCheckoutComponent
{
    /**
     * Indicates whether the component should be rendered.
     */
    public bool $shouldRender = false;

    /**
     * Indicates whether the activity questions are being loaded.
     */
    public bool $isLoadingQuestions = false;

    /**
     * Indicates whether loading the activity questions has failed.
     */
    public bool $hasLoadingFailed = false;

    /**
     * The number of times the questions started loading, which gives every loader a new key, so it always loads.
     */
    public int $loadingAttempt = 0;

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
        $this->claimCustomerData();

        if (! $this->shouldRender) {
            dispatch(new VerifyAvailabilityJob($this->getParams()));
            $this->markAsCompletedAdnCollapse(Section::Activity);
            $this->dispatch(Section::Activity->value);

            return;
        }

        // Show the loader right away, the questions are loaded by the view in the next request.
        $this->hasLoadingFailed = false;
        $this->isLoadingQuestions = true;
        $this->loadingAttempt++;
    }

    /**
     * Load the activity questions, then open the section, or skip it when there are no questions.
     */
    public function loadQuestions(): void
    {
        if (! $this->isLoadingQuestions) {
            return;
        }

        try {
            (new VerifyAvailabilityJob($this->getParams()))->handle();

            // A failed availability check is cached instead of thrown, so it is checked here.
            if (AvailabilityFacade::getCachedStatus($this->getParams()) !== 200) {
                throw new UnavailableServiceException(AvailabilityFacade::getCachedResult($this->getParams()));
            }

            $this->activityQuestions = NezasaConnector::make()->checkout()->activityQuestions($this->checkoutId)->dto();
        } catch (Throwable $e) {
            // Failed responses throw HTTP exceptions, which are not reported on their own.
            report(new RuntimeException("Loading the activity questions of checkout {$this->checkoutId} failed.", previous: $e));

            // The loader is replaced by the retry button, as a failed request would leave it spinning.
            $this->isLoadingQuestions = false;
            $this->hasLoadingFailed = true;

            return;
        }

        $this->currentActivity = 0;
        $this->isLoadingQuestions = false;

        // The customer may have reopened the traveller details while the questions were loading.
        $this->model->refresh();

        if (! $this->model->isCompleted(Section::Traveller)) {
            return;
        }

        if ($this->activityQuestions->isEmpty()) {
            $this->shouldRender = false;

            $this->next();
        } elseif (! $this->isCompleted) {
            $this->expand(Section::Activity);
        }

        $this->fillResult();
    }

    /**
     * Load the activity questions again after loading them has failed.
     */
    public function retryLoadingQuestions(): void
    {
        $this->listen();
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

        $this->claimCustomerData();

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
                AnswerValidationEnum::Double => 'numeric',
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
                $answer = $this->ownsCustomerData()
                    ? $this->model->getAnswer($component->componentId, $question->refId)
                    : null;

                $this->result[$component->componentId][$question->refId] = $answer;
            }
        }

        dispatch(new UpdateAnswerActivityQuestionJob($this->checkoutId, $this->activityQuestions));
    }

    /**
     * Reset the section.
     *
     * @param  array<int, string>  $sections
     */
    #[On('sections-reset')]
    public function resetSection(array $sections): void
    {
        if (! in_array(Section::Activity->value, $sections, true)) {
            return;
        }

        $this->isCompleted = false;
        $this->isExpanded = false;
        $this->isLoadingQuestions = false;
        $this->hasLoadingFailed = false;
    }
}
