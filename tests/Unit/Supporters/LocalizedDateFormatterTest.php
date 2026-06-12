<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Supporters\LocalizedDateFormatter;

it('formats travel dates with German weekday and month labels', function (): void {
    app()->setLocale('de');

    expect(LocalizedDateFormatter::short(CarbonImmutable::parse('2026-09-18'), true))
        ->toBe('Fr, 18. Sep 2026')
        ->and(LocalizedDateFormatter::short(CarbonImmutable::parse('2026-10-02'), true))
        ->toBe('Fr, 2. Okt 2026');
});

it('returns localized month names for date selectors', function (): void {
    app()->setLocale('de');

    expect(LocalizedDateFormatter::monthName(5))->toBe('Mai')
        ->and(LocalizedDateFormatter::monthName(6))->toBe('Juni')
        ->and(LocalizedDateFormatter::monthName(7))->toBe('Juli');
});
