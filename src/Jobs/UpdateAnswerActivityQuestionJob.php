<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Nezasa\Checkout\Models\Checkout;

class UpdateAnswerActivityQuestionJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  Collection<int, ActivityQuestionResponse>  $newComponents
     */
    public function __construct(
        public string $checkoutId,
        public Collection $newComponents
    ) {}

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5($this->checkoutId.'-'.$this->newComponents->toJson());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->firstOrFail(['checkout_id' => $this->checkoutId]);

        $new = [];

        foreach ($model->data['activityAnswers'] as $itemId => $item) {
            $newComponent = $this->newComponents->where('componentId', $itemId)->first();
            if ($newComponent) {
                foreach ($item as $questionId => $answer) {
                    if ($newComponent->questions->where('refId', $questionId)->isNotEmpty()) {
                        $new[$itemId][$questionId] = $answer;
                    }
                }
            }
        }

        $model->updateData(['activityAnswers' => $new]);
    }
}
