<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Carbon;

uses(Tests\TestCase::class)->in(__DIR__);

function fakeCarbon(int $year = 2025, int $month = 8, int $day = 27, int $hour = 11, int $minute = 20, int $second = 19): void
{
    Carbon::setTestNow(
        Carbon::create($year, $month, $day, $hour, $minute, $second)
    );
}
