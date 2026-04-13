<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Exceptions;

use Exception;

class ErgoException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(sprintf('Ergo error "%s"', $message));
    }
}
