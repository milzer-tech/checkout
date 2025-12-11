<?php

use Nezasa\Checkout\Actions\Operation\SaveSectionStatusAction;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Livewire\BaseCheckoutComponent;
use Nezasa\Checkout\Models\Checkout;

class TestableBaseCheckoutComponent extends BaseCheckoutComponent
{
    public function callMarkAsCompleted(Section $section): void
    {
        $this->markAsCompleted($section);
    }

    public function callMarkAsNotCompleted(Section $section): void
    {
        $this->markAsNotCompleted($section);
    }

    public function callGetQueryParams(): array
    {
        return $this->getQueryParams();
    }
}

beforeEach(function (): void {
    $this->component = new TestableBaseCheckoutComponent;
    $this->component->checkoutId = 'co-123';
    $this->component->itineraryId = 'it-999';
    $this->component->origin = 'app';
    $this->component->lang = 'en';
    $this->component->isExpanded = false;
    $this->component->isCompleted = false;

    // Seed a minimal checkout model required by BaseCheckoutComponent
    $this->component->model = Checkout::create([
        'checkout_id' => 'co-123',
        'itinerary_id' => 'it-999',
        'data' => [
            'status' => [],
        ],
    ]);
});

it('expand accepts string section, sets isExpanded=true and calls SaveSectionStatusAction with current completion state', function (): void {
    $state = (object) ['called' => false, 'received' => []];
    app()->bind(SaveSectionStatusAction::class, fn (): object => new readonly class($state)
    {
        public function __construct(private object $state) {}

        public function run($model, $section, $isCompleted, $isExpanded): void
        {
            $this->state->called = true;
            $this->state->received = ['model' => $model, 'section' => $section, 'isCompleted' => $isCompleted, 'isExpanded' => $isExpanded];
        }
    });

    $this->component->expand('contact');

    expect($this->component->isExpanded)->toBeTrue()
        ->and($this->component->isCompleted)->toBeFalse()
        ->and($state->called)->toBeTrue()
        ->and($state->received['model']->is($this->component->model))->toBeTrue()
        ->and($state->received['section'])->toBe(Section::Contact)
        ->and($state->received['isCompleted'])->toBeFalse()
        ->and($state->received['isExpanded'])->toBeTrue();
});

it('collapse sets isExpanded=false and calls SaveSectionStatusAction', function (): void {
    $state = (object) ['called' => false, 'received' => []];
    app()->bind(SaveSectionStatusAction::class, fn (): object => new readonly class($state)
    {
        public function __construct(private object $state) {}

        public function run($model, $section, $isCompleted, $isExpanded): void
        {
            $this->state->called = true;
            $this->state->received = ['model' => $model, 'section' => $section, 'isCompleted' => $isCompleted, 'isExpanded' => $isExpanded];
        }
    });

    $this->component->isExpanded = true;

    $this->component->collapse(Section::Promo);

    expect($this->component->isExpanded)->toBeFalse()
        ->and($state->called)->toBeTrue()
        ->and($state->received['section'])->toBe(Section::Promo)
        ->and($state->received['isCompleted'])->toBeFalse()
        ->and($state->received['isExpanded'])->toBeFalse();
});

it('markAsCompletedAdnCollapse marks completed and collapses, then calls SaveSectionStatusAction', function (): void {
    $state = (object) ['called' => false, 'received' => []];
    app()->bind(SaveSectionStatusAction::class, fn (): object => new readonly class($state)
    {
        public function __construct(private object $state) {}

        public function run($model, $section, $isCompleted, $isExpanded): void
        {
            $this->state->called = true;
            $this->state->received = ['model' => $model, 'section' => $section, 'isCompleted' => $isCompleted, 'isExpanded' => $isExpanded];
        }
    });

    $this->component->isExpanded = true;

    $this->component->markAsCompletedAdnCollapse(Section::Traveller);

    expect($this->component->isCompleted)->toBeTrue()
        ->and($this->component->isExpanded)->toBeFalse()
        ->and($state->called)->toBeTrue()
        ->and($state->received['section'])->toBe(Section::Traveller)
        ->and($state->received['isCompleted'])->toBeTrue()
        ->and($state->received['isExpanded'])->toBeFalse();
});

it('markAsCompleted sets isCompleted=true and calls action', function (): void {
    $state = (object) ['called' => false, 'received' => []];
    app()->bind(SaveSectionStatusAction::class, fn (): object => new readonly class($state)
    {
        public function __construct(private object $state) {}

        public function run($model, $section, $isCompleted, $isExpanded): void
        {
            $this->state->called = true;
            $this->state->received = ['model' => $model, 'section' => $section, 'isCompleted' => $isCompleted, 'isExpanded' => $isExpanded];
        }
    });

    $this->component->callMarkAsCompleted(Section::Summary);

    expect($this->component->isCompleted)->toBeTrue()
        ->and($state->called)->toBeTrue()
        ->and($state->received['section'])->toBe(Section::Summary)
        ->and($state->received['isCompleted'])->toBeTrue()
        ->and($state->received['isExpanded'])->toBeFalse();
});

it('markAsNotCompleted sets isCompleted=false and calls action', function (): void {
    $state = (object) ['called' => false, 'received' => []];
    app()->bind(SaveSectionStatusAction::class, fn (): object => new readonly class($state)
    {
        public function __construct(private object $state) {}

        public function run($model, $section, $isCompleted, $isExpanded): void
        {
            $this->state->called = true;
            $this->state->received = ['model' => $model, 'section' => $section, 'isCompleted' => $isCompleted, 'isExpanded' => $isExpanded];
        }
    });

    $this->component->isCompleted = true;

    $this->component->callMarkAsNotCompleted(Section::AdditionalService);

    expect($this->component->isCompleted)->toBeFalse()
        ->and($state->called)->toBeTrue()
        ->and($state->received['section'])->toBe(Section::AdditionalService)
        ->and($state->received['isCompleted'])->toBeFalse()
        ->and($state->received['isExpanded'])->toBeFalse();
});

it('getQueryParams returns expected URL parameters', function (): void {
    $params = $this->component->callGetQueryParams();

    expect($params)->toBe([
        'checkoutId' => 'co-123',
        'itineraryId' => 'it-999',
        'origin' => 'app',
        'lang' => 'en',
    ]);
});
