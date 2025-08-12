<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

final class OppwaResultResponseEntity extends BaseDto
{
    /**
     * Create a new instance of OppwaResultResponseEntity.
     */
    public function __construct(
        public string $code,
        public string $description,
    ) {}
}
