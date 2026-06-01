<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceOfferDocumentLinkDto extends BaseDto
{
    public function __construct(
        public string $label,
        public string $url,
        public ?string $type = null,
    ) {}
}
