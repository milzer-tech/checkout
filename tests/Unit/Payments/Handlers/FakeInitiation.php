<?php

declare(strict_types=1);

namespace Tests\Unit\Payments\Handlers;

final class FakeInitiation
{
    public static function name(): string
    {
        return 'Fake Gateway';
    }
}
