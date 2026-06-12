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
        $date = Carbon::instance($date);

        $date->locale(app()->getLocale());

        return $date->isoFormat((string) trans("checkout::page.dates.formats.$format"));
    }

    public static function monthName(int $month): string
    {
        $date = Carbon::create(year: 2000, month: $month, day: 1);

        $date->locale(app()->getLocale());

        return $date->monthName;
    }
}
