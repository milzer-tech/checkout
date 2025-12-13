<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Facades;

use Illuminate\Support\Facades\Facade;
use Nezasa\Checkout\Supporters\InsuranceSupporter;

final class InsuranceFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return InsuranceSupporter::class;
    }
}
