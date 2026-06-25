<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;

class PassolutionSectionResponse extends BaseDto
{
    public function __construct(
        public ?string $language = null,
        public ?string $title = null,
        public ?string $content = null,
        public ?string $updatedAt = null,
    ) {}
}
