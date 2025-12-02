<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

final class TextSectionResponseEntity extends BaseDto
{
    /**
     * Create a new instance of TextSectionResponseEntity
     */
    public function __construct(
        public string $header,
        public string $text,
        public ?string $checkBoxText = null,
        public ?string $supplierId = null

    ) {}

}
