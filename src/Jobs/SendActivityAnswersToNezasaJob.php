<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto;
use Nezasa\Checkout\Models\Checkout;

final class SendActivityAnswersToNezasaJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $checkoutId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->where('checkout_id', $this->checkoutId)->firstOrFail();

        $answers = collect();

        foreach ($model->data['activityAnswers'] as $componentId => $questions) {
            foreach ($questions as $questionId => $answer) {
                $answers[] = new AnswerActivityQuestionPayloadDto(
                    componentId: $componentId,
                    questionRefId: $questionId,
                    answer: $answer
                );
            }
        }
        if ($answers->isNotEmpty()) {
            NezasaConnector::make()->checkout()->answerActivityQuestions($this->checkoutId, $answers);
        }
    }
}
