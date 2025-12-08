<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Facades;

use Illuminate\Support\Facades\Facade;
use Nezasa\Checkout\Supporters\AvailabilitySupporter;

final class AvailabilityFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return AvailabilitySupporter::class;
    }
}
