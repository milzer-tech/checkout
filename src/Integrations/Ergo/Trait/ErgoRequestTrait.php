<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Trait;

trait ErgoRequestTrait
{
    protected function getSupplierNameForLogging(): string
    {
        return 'ergo';
    }

    protected function getLogName(): string
    {
        return 'insurance';
    }
}
