<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetActivityQuestionsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\VerifyAvailabilityRequest;
use Nezasa\Checkout\Jobs\SendActivityAnswersToNezasaJob;
use Nezasa\Checkout\Jobs\VerifyAvailabilityJob;
use Nezasa\Checkout\Livewire\ActivitySection;
use Nezasa\Checkout\Models\Checkout;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * Create a checkout whose contact details are completed and whose traveller details are still open.
 *
 * @param  array<string, array<string, string>>  $activityAnswers
 */
function activitySectionCheckout(array $activityAnswers = []): Checkout
{
    $status = Checkout::buildSectionStatus();
    $status[Section::Contact->value] = ['isExpanded' => false, 'isCompleted' => true];
    $status[Section::Traveller->value] = ['isExpanded' => true, 'isCompleted' => false];

    return Checkout::create([
        'checkout_id' => 'checkout-456',
        'itinerary_id' => 'itinerary-123',
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'contact' => [],
            'paxInfo' => [],
            'activityAnswers' => $activityAnswers,
            'acceptedTerms' => [],
            'numberOfPax' => 2,
            'status' => $status,
            'insurance' => null,
        ],
    ]);
}

/**
 * @return array<string, mixed>
 */
function activitySectionProps(Checkout $checkout, bool $shouldRender = true): array
{
    return [
        'shouldRender' => $shouldRender,
        'model' => $checkout,
        'checkoutId' => $checkout->checkout_id,
        'itineraryId' => $checkout->itinerary_id,
        'origin' => $checkout->origin,
        'lang' => $checkout->lang,
        'isCompleted' => $checkout->isCompleted(Section::Activity),
        'isExpanded' => $checkout->isExpanded(Section::Activity),
    ];
}

/**
 * Store the traveller details as completed, as the traveller section does before it announces it.
 */
function finishTravellerStep(Checkout $checkout): void
{
    $checkout->updateData([
        'status.traveller.isCompleted' => true,
        'status.traveller.isExpanded' => false,
    ]);
}

/**
 * Fake the Nezasa calls of the activity section, replacing the responses faked before.
 */
function fakeActivityQuestions(?MockResponse $questions = null): MockClient
{
    MockClient::destroyGlobal();

    return MockClient::global([
        VerifyAvailabilityRequest::class => MockResponse::make([], 200),
        GetActivityQuestionsRequest::class => $questions ?? mockFixture('get_activity_question_response'),
    ]);
}

beforeEach(function (): void {
    Config::set('checkout.customer_data.restore_always', true);

    Queue::fake();
});

it('hides the activity section until it is known that the activities have questions', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();

    Livewire::test(ActivitySection::class, activitySectionProps($checkout))
        ->assertDontSee(trans('checkout::page.trip_details.activity_details'))
        ->assertDontSeeHtml('wire:init="loadQuestions"');

    $mockClient->assertNothingSent();
});

it('lets the browser show a loader as soon as the traveller step is finished', function (): void {
    fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout))
        ->assertSeeHtml('wire:key="activity-questions-placeholder"')
        ->assertSeeHtml('x-on:traveller.window="waiting = true"');
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->assertDontSeeHtml('wire:key="activity-questions-placeholder"')
        ->assertSeeHtml('wire:key="activity-questions-loader-1"');
});

it('shows a loader as soon as the traveller step is finished', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->assertSet('isLoadingQuestions', true)
        ->assertSet('isExpanded', false)
        ->assertSeeHtml('wire:init="loadQuestions"')
        ->assertSee(trans('checkout::page.trip_details.loading'))
        ->assertDontSee(trans('checkout::page.trip_details.activity_details'));

    $mockClient->assertNothingSent();
});

it('opens the activity section once its questions are loaded', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('isExpanded', true)
        ->assertSet('isCompleted', false)
        ->assertSee(trans('checkout::page.trip_details.activity_details'))
        ->assertSee('Arsenal FC vs AFC Bournemouth')
        ->assertSeeHtml('wire:submit="nextActivity(0)"')
        ->assertDontSeeHtml('wire:init="loadQuestions"');

    $mockClient->assertSent(VerifyAvailabilityRequest::class);
    $mockClient->assertSent(GetActivityQuestionsRequest::class);

    expect($checkout->refresh()->isExpanded(Section::Activity))->toBeTrue();
});

it('opens the activity section when all the answers are already stored', function (): void {
    fakeActivityQuestions();
    $checkout = activitySectionCheckout([
        '69205aac5800004e8db60aba' => ['pax-1-countryCode' => 'DEU', 'pax-2-countryCode' => 'DEU'],
        '69205aac5800004e8db60abad' => ['pax-1-countryCode' => 'DEU', 'pax-2-countryCode' => 'DEU'],
    ]);
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertSet('isExpanded', true)
        ->assertSet('isCompleted', false)
        ->assertSet('result.69205aac5800004e8db60aba.pax-1-countryCode', 'DEU')
        ->assertSet('result.69205aac5800004e8db60abad.pax-2-countryCode', 'DEU');

    $checkout->refresh();

    expect($checkout->isExpanded(Section::Activity))->toBeTrue()
        ->and($checkout->isCompleted(Section::Activity))->toBeFalse();
});

it('opens the activity section at its first activity each time the traveller step is finished again', function (): void {
    fakeActivityQuestions();
    $checkout = activitySectionCheckout([
        '69205aac5800004e8db60aba' => ['pax-1-countryCode' => 'DEU', 'pax-2-countryCode' => 'DEU'],
        '69205aac5800004e8db60abad' => ['pax-1-countryCode' => 'DEU', 'pax-2-countryCode' => 'DEU'],
    ]);
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->call('nextActivity', 0)
        ->assertSet('currentActivity', 1)
        ->assertSeeHtml('wire:submit="nextActivity(1)"');

    // The customer edits the traveller details, which resets the sections after them, and finishes them again.
    $component->dispatch('sections-reset', sections: [Section::Activity->value, Section::Promo->value])
        ->assertSet('isExpanded', false)
        ->dispatch(Section::Traveller->value)
        ->assertSet('isLoadingQuestions', true)
        ->assertSeeHtml('wire:key="activity-questions-loader-2"')
        ->assertDontSee('Arsenal FC vs AFC Bournemouth')
        ->call('loadQuestions')
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('isExpanded', true)
        ->assertSet('currentActivity', 0);
});

it('hides the activity section and opens the next one when the activities have no questions', function (): void {
    fakeActivityQuestions(MockResponse::make([], 200));
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertSet('shouldRender', false)
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('isCompleted', true)
        ->assertSet('isExpanded', false)
        ->assertDispatched(Section::Activity->value)
        ->assertDontSee(trans('checkout::page.trip_details.activity_details'))
        ->assertDontSeeHtml('wire:init="loadQuestions"');

    Queue::assertPushed(SendActivityAnswersToNezasaJob::class);

    $checkout->refresh();

    expect($checkout->isCompleted(Section::Activity))->toBeTrue()
        ->and($checkout->isExpanded(Section::Activity))->toBeFalse();
});

it('skips the activity section without a loader when the itinerary has no activities', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout, shouldRender: false));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('isCompleted', true)
        ->assertSet('isExpanded', false)
        ->assertDispatched(Section::Activity->value)
        ->assertDontSee(trans('checkout::page.trip_details.activity_details'))
        ->assertDontSeeHtml('wire:init="loadQuestions"')
        ->assertDontSeeHtml('x-on:traveller.window');

    Queue::assertPushed(VerifyAvailabilityJob::class);
    $mockClient->assertNothingSent();
});

it('offers to load the questions again when loading them fails', function (): void {
    Exceptions::fake();
    fakeActivityQuestions(MockResponse::make([], 500));
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertStatus(200)
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('hasLoadingFailed', true)
        ->assertSee(trans('checkout::page.trip_details.activity_loading_failed'))
        ->assertSeeHtml('wire:click="retryLoadingQuestions"')
        ->assertDontSeeHtml('wire:init="loadQuestions"');

    Exceptions::assertReported(fn (RuntimeException $e): bool => $e->getPrevious() instanceof NotFoundException);

    fakeActivityQuestions();

    $component->call('retryLoadingQuestions')
        ->assertSet('hasLoadingFailed', false)
        ->assertSet('isLoadingQuestions', true)
        ->assertSeeHtml('wire:init="loadQuestions"')
        ->assertDontSee(trans('checkout::page.trip_details.activity_loading_failed'))
        ->call('loadQuestions')
        ->assertSet('isExpanded', true)
        ->assertSee('Arsenal FC vs AFC Bournemouth');
});

it('loads the questions again when the traveller step is finished again after loading them failed', function (): void {
    Exceptions::fake();
    fakeActivityQuestions(MockResponse::make([], 500));
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertSet('hasLoadingFailed', true);

    fakeActivityQuestions();

    // The customer edits the traveller details, which resets the sections after them, and finishes them again.
    $component->dispatch('sections-reset', sections: [Section::Activity->value, Section::Promo->value])
        ->assertSet('hasLoadingFailed', false)
        ->assertDontSee(trans('checkout::page.trip_details.activity_loading_failed'))
        ->dispatch(Section::Traveller->value)
        ->assertSet('isLoadingQuestions', true)
        ->call('loadQuestions')
        ->assertSet('isExpanded', true)
        ->assertSee('Arsenal FC vs AFC Bournemouth');
});

it('keeps the activity section closed when the traveller details are reopened while its questions load', function (): void {
    $checkout = activitySectionCheckout();

    MockClient::global([
        VerifyAvailabilityRequest::class => MockResponse::make([], 200),
        // The traveller section stores its reopening in a parallel request while Nezasa answers.
        GetActivityQuestionsRequest::class => function () use ($checkout): MockResponse {
            Checkout::query()->findOrFail($checkout->id)->updateData([
                'status.traveller.isCompleted' => false,
                'status.traveller.isExpanded' => true,
            ]);

            return mockFixture('get_activity_question_response');
        },
    ]);

    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->assertSet('isLoadingQuestions', false)
        ->assertSet('isExpanded', false)
        ->assertNotDispatched('toast');

    $checkout->refresh();

    expect($checkout->isCompleted(Section::Traveller))->toBeFalse()
        ->and($checkout->isExpanded(Section::Traveller))->toBeTrue()
        ->and($checkout->isExpanded(Section::Activity))->toBeFalse();
});

it('does not load the questions when the traveller details are reopened before they load', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->dispatch('sections-reset', sections: [Section::Activity->value, Section::Promo->value])
        ->assertSet('isLoadingQuestions', false)
        ->assertDontSeeHtml('wire:init="loadQuestions"')
        ->call('loadQuestions');

    $mockClient->assertNothingSent();
});

it('does not reopen a completed activity section or reset the sections after it', function (): void {
    fakeActivityQuestions();
    $checkout = activitySectionCheckout();
    $checkout->updateData([
        'status' => array_map(fn (): array => ['isExpanded' => false, 'isCompleted' => true], Checkout::buildSectionStatus()),
    ]);

    Livewire::test(ActivitySection::class, activitySectionProps($checkout))
        ->assertSet('isLoadingQuestions', true)
        ->call('loadQuestions')
        ->assertSet('isCompleted', true)
        ->assertSet('isExpanded', false)
        ->assertNotDispatched('sections-reset');

    $checkout->refresh();

    expect($checkout->isCompleted(Section::Activity))->toBeTrue()
        ->and($checkout->isCompleted(Section::Promo))->toBeTrue()
        ->and($checkout->isCompleted(Section::Insurance))->toBeTrue();
});

it('accepts a decimal number as the answer to a decimal question', function (): void {
    fakeActivityQuestions(MockResponse::make([[
        'componentId' => 'activity-with-decimal',
        'productName' => 'Diving course',
        'questions' => [[
            'refId' => 'weight',
            'question' => 'Weight in kg',
            'required' => true,
            'answerValidation' => 'double',
        ]],
    ]], 200));
    $checkout = activitySectionCheckout();
    $component = Livewire::test(ActivitySection::class, activitySectionProps($checkout));
    finishTravellerStep($checkout);

    $component->dispatch(Section::Traveller->value)
        ->call('loadQuestions')
        ->set('result.activity-with-decimal.weight', '72.5')
        ->assertHasNoErrors()
        ->set('result.activity-with-decimal.weight', 'heavy')
        ->assertHasErrors(['result.activity-with-decimal.weight' => 'numeric'])
        ->set('result.activity-with-decimal.weight', '80')
        ->call('nextActivity', 0)
        ->assertHasNoErrors()
        ->assertSet('isCompleted', true);
});

it('does not load the questions unless the section waits for them', function (): void {
    $mockClient = fakeActivityQuestions();
    $checkout = activitySectionCheckout();

    Livewire::test(ActivitySection::class, activitySectionProps($checkout))
        ->call('loadQuestions')
        ->assertSet('isLoadingQuestions', false);

    $mockClient->assertNothingSent();
});
