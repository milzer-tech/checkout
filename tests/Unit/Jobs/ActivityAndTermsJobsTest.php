<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\QuestionResponseEntity;
use Nezasa\Checkout\Jobs\SaveAnswerActivityQuestionJob;
use Nezasa\Checkout\Jobs\SaveTermAgreementJob;
use Nezasa\Checkout\Jobs\UpdateAnswerActivityQuestionJob;
use Nezasa\Checkout\Models\Checkout;

function activityJobCheckout(array $data = []): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'data' => array_replace_recursive([
            'activityAnswers' => [],
            'acceptedTerms' => [],
        ], $data),
    ]);
}

it('stores activity answers under component and question ids with a stable unique id', function (): void {
    $checkout = activityJobCheckout();
    $answer = new AnswerActivityQuestionPayloadDto('activity-1', 'question-1', 'yes');
    $job = new SaveAnswerActivityQuestionJob($checkout->checkout_id, $answer);

    $job->handle();

    $checkout->refresh();

    expect($job->uniqueId())->toBe(md5($checkout->checkout_id.'-'.$answer->toJson()))
        ->and(data_get($checkout->data, 'activityAnswers.activity-1.question-1'))->toBe('yes');
});

it('prunes stored activity answers that are no longer present in latest activity questions', function (): void {
    $checkout = activityJobCheckout([
        'activityAnswers' => [
            'activity-1' => [
                'keep-question' => 'A',
                'drop-question' => 'B',
            ],
            'removed-activity' => [
                'question' => 'C',
            ],
        ],
    ]);
    $components = new Collection([
        new ActivityQuestionResponse(
            componentId: 'activity-1',
            productName: 'Activity',
            questions: new Collection([
                new QuestionResponseEntity(
                    refId: 'keep-question',
                    question: 'Keep?',
                    required: true,
                ),
            ])
        ),
    ]);
    $job = new UpdateAnswerActivityQuestionJob($checkout->checkout_id, $components);

    $job->handle();

    $checkout->refresh();

    expect($job->uniqueId())->toBe(md5($checkout->checkout_id.'-'.$components->toJson()))
        ->and($checkout->data['activityAnswers'])->toBe([
            'activity-1' => [
                'keep-question' => 'A',
            ],
        ]);
});

it('stores accepted terms by their exact nested key', function (): void {
    $checkout = activityJobCheckout();
    $job = new SaveTermAgreementJob($checkout->checkout_id, 'acceptedTerms.supplier-1', true);

    $job->handle();

    $checkout->refresh();

    expect($job->uniqueId())->toBe(md5($checkout->checkout_id.'-acceptedTerms.supplier-1'))
        ->and(data_get($checkout->data, 'acceptedTerms.supplier-1'))->toBeTrue();
});
