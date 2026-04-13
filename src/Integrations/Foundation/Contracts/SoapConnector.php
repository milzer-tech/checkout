<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Foundation\Contracts;

use Soap\Encoding\Driver;

interface SoapConnector
{
    public function soapDriver(): Driver;

    public function prepareSoapPayload(mixed $payload): mixed;
}
