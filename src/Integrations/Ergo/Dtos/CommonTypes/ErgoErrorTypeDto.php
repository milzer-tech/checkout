<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoErrorTypeDto extends Data
{
    public function __construct(
        public ?string $ErrorCode,
        public ?string $ErrorMessage,
        public ?string $language,
        public ?string $type
    ) {}
}
