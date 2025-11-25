<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

final class AnswerOptionResponseEntity extends BaseDto
{
    /**
     * Create a new instance of AnswerOptionResponseEntity.
     */
    public function __construct(
        public string $refId,
        public string $displayName
    ) {}

}
