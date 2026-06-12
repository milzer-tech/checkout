<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use DateTimeInterface;
use Illuminate\Support\Carbon;

final class LocalizedDateFormatter
{
    public static function short(DateTimeInterface $date, bool $withYear = false): string
    {
        $format = $withYear ? 'short_with_year' : 'short';
        $locale = app()->getLocale();

        return Carbon::instance($date)
            ->locale($locale)
            ->isoFormat(trans("checkout::page.dates.formats.$format"));
    }

    public static function monthName(int $month): string
    {
        return Carbon::create(null, $month, 1)
            ->locale(app()->getLocale())
            ->monthName;
    }
}
