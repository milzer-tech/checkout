<?php

use Illuminate\Support\Facades\Bus;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Jobs\SaveSectionStatusJob;
use Nezasa\Checkout\Livewire\BaseCheckoutComponent;

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
});

it('expand accepts string section, sets isExpanded=true and dispatches SaveSectionStatusJob with current completion state', function (): void {
    Bus::fake();

    $this->component->expand('contact');

    expect($this->component->isExpanded)->toBeTrue()
        ->and($this->component->isCompleted)->toBeFalse();

    // Assert dispatch
    Bus::assertDispatched(SaveSectionStatusJob::class, function (SaveSectionStatusJob $job): bool {
        return $job->checkoutId === 'co-123'
            && $job->section === Section::Contact
            && $job->isCompleted === false
            && $job->isExpanded === true;
    });
});

it('collapse sets isExpanded=false and dispatches SaveSectionStatusJob', function (): void {
    Bus::fake();

    $this->component->isExpanded = true;

    $this->component->collapse(Section::Promo);

    expect($this->component->isExpanded)->toBeFalse();

    Bus::assertDispatched(SaveSectionStatusJob::class, function (SaveSectionStatusJob $job): bool {
        return $job->checkoutId === 'co-123'
            && $job->section === Section::Promo
            && $job->isCompleted === false
            && $job->isExpanded === false;
    });
});

it('markAsCompletedAdnCollapse marks completed and collapses, then dispatches SaveSectionStatusJob', function (): void {
    Bus::fake();

    $this->component->isExpanded = true;

    $this->component->markAsCompletedAdnCollapse(Section::Traveller);

    expect($this->component->isCompleted)->toBeTrue()
        ->and($this->component->isExpanded)->toBeFalse();

    Bus::assertDispatched(SaveSectionStatusJob::class, function (SaveSectionStatusJob $job): bool {
        return $job->checkoutId === 'co-123'
            && $job->section === Section::Traveller
            && $job->isCompleted === true
            && $job->isExpanded === false;
    });
});

it('markAsCompleted sets isCompleted=true and dispatches', function (): void {
    Bus::fake();

    $this->component->callMarkAsCompleted(Section::Summary);

    expect($this->component->isCompleted)->toBeTrue();

    Bus::assertDispatched(SaveSectionStatusJob::class, function (SaveSectionStatusJob $job): bool {
        return $job->checkoutId === 'co-123'
            && $job->section === Section::Summary
            && $job->isCompleted === true
            && $job->isExpanded === false; // unchanged
    });
});

it('markAsNotCompleted sets isCompleted=false and dispatches', function (): void {
    Bus::fake();

    $this->component->isCompleted = true;

    $this->component->callMarkAsNotCompleted(Section::AdditionalService);

    expect($this->component->isCompleted)->toBeFalse();

    Bus::assertDispatched(SaveSectionStatusJob::class, function (SaveSectionStatusJob $job): bool {
        return $job->checkoutId === 'co-123'
            && $job->section === Section::AdditionalService
            && $job->isCompleted === false
            && $job->isExpanded === false;
    });
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
