<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto;
use Nezasa\Checkout\Models\Checkout;

class SaveAnswerActivityQuestionJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $checkoutId,
        public AnswerActivityQuestionPayloadDto $answer
    ) {}

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5($this->checkoutId.'-'.$this->answer->toJson());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->firstOrCreate(['checkout_id' => $this->checkoutId]);

        $key = implode('.', ['activityAnswers', $this->answer->componentId, $this->answer->questionRefId]);

        $model->updateData([$key => $this->answer->answer]);
    }
}
