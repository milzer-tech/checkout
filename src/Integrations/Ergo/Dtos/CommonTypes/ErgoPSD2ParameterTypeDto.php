<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

class ErgoPSD2ParameterTypeDto
{
    public function __construct(
        public string $cavv,
        public string $eci,
        public string $threeDSVersion,
        public string $threeDSTransactionId,
        public string $psd2Extensions
    ) {}
}
