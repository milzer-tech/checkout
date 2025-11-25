<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class AnswerActivityQuestionPayloadDto extends BaseDto
{
    /**
     * Create a new instance of AnswerActivityQuestionPayloadDto.
     */
    public function __construct(
        public string $componentId,
        public string $questionRefId,
        public string $answer
    ) {}
}
