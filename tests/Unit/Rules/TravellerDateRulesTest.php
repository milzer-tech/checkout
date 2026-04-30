<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Dtos\View\ShowTraveller;
use Nezasa\Checkout\Rules\BirthDateRule;
use Nezasa\Checkout\Rules\PassportExpirationDateRule;

function collectRuleFailures(): array
{
    $collector = (object) ['failures' => []];
    $fail = function (string $message) use ($collector): object {
        $collector->failures[] = $message;

        return new class
        {
            /**
             * @param  array<string, mixed>  $replace
             */
            public function translate(array $replace = []): void {}
        };
    };

    return [$collector, $fail];
}

it('validates adult and child birth dates against allocation ages', function (): void {
    Config::set('checkout.distribution.max_child_age', 17);
    $rule = new BirthDateRule(
        startDate: CarbonImmutable::parse('2025-09-01'),
        allocatedPax: [
            'rooms' => [
                ['adults' => 1, 'childAges' => [8]],
            ],
        ]
    );
    $rule->setData([
        'paxInfo' => [
            [
                [
                    'showTraveller' => new ShowTraveller(isAdult: true),
                    'birthDate' => ['year' => 1990, 'month' => 1, 'day' => 1],
                ],
                [
                    'showTraveller' => new ShowTraveller(isAdult: false, age: 8),
                    'birthDate' => ['year' => 2017, 'month' => 9, 'day' => 1],
                ],
            ],
        ],
    ]);
    [$collector, $fail] = collectRuleFailures();

    $rule->validate('paxInfo.0.0.birthDate.day', 1, $fail);
    $rule->validate('paxInfo.0.1.birthDate.day', 1, $fail);

    expect($collector->failures)->toBe([]);
});

it('fails birth date validation for adults who are too young and children with wrong age', function (): void {
    Config::set('checkout.distribution.max_child_age', 17);
    $rule = new BirthDateRule(
        startDate: CarbonImmutable::parse('2025-09-01'),
        allocatedPax: [
            'rooms' => [
                ['adults' => 1, 'childAges' => [8]],
            ],
        ]
    );
    $rule->setData([
        'paxInfo' => [
            [
                [
                    'showTraveller' => new ShowTraveller(isAdult: true),
                    'birthDate' => ['year' => 2010, 'month' => 9, 'day' => 1],
                ],
                [
                    'showTraveller' => new ShowTraveller(isAdult: false, age: 8),
                    'birthDate' => ['year' => 2015, 'month' => 9, 'day' => 1],
                ],
            ],
        ],
    ]);
    [$collector, $fail] = collectRuleFailures();

    $rule->validate('paxInfo.0.0.birthDate.day', 1, $fail);
    $rule->validate('paxInfo.0.1.birthDate.day', 1, $fail);

    expect($collector->failures)->toContain('checkout::input.validations.adult_age')
        ->and($collector->failures)->toContain('checkout::input.validations.child_age_diff');
});

it('requires passport expiration to be after itinerary end date', function (): void {
    $rule = new PassportExpirationDateRule(CarbonImmutable::parse('2025-09-10'));
    $rule->setData([
        'paxInfo' => [
            [
                [
                    'passportExpirationDate' => ['year' => 2025, 'month' => 9, 'day' => 11],
                ],
                [
                    'passportExpirationDate' => ['year' => 2025, 'month' => 9, 'day' => 9],
                ],
            ],
        ],
    ]);

    [$validCollector, $validFail] = collectRuleFailures();
    $rule->validate('paxInfo.0.0.passportExpirationDate.day', 11, $validFail);

    [$invalidCollector, $invalidFail] = collectRuleFailures();
    $rule->validate('paxInfo.0.1.passportExpirationDate.day', 9, $invalidFail);

    expect($validCollector->failures)->toBe([])
        ->and($invalidCollector->failures)->toBe(['checkout::input.validations.passportExpirationDate']);
});
