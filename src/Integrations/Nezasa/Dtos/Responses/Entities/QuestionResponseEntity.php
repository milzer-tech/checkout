<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerInputEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerValidationEnum;

class QuestionResponseEntity extends BaseDto
{
    /**
     * Create a new instance of QuestionResponseEntity.
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Activities/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1activity-questions/get
     *
     * @param  Collection<int, AnswerOptionResponseEntity>  $answerOptions
     */
    public function __construct(
        public string $refId,
        public string $question,
        public bool $required,
        public ?string $answer = null,
        public ?string $placeholder = null,
        public Collection $answerOptions = new Collection,
        public ?AnswerValidationEnum $answerValidation = null,
    ) {}

    /**
     * Get the input type for the question.
     */
    public function getInputType(): AnswerInputEnum
    {
        if ($this->answerOptions->isNotEmpty()) {
            return AnswerInputEnum::Select;
        }

        if ($this->answerValidation?->isBoolean()) {
            return AnswerInputEnum::Checkbox;
        }

        return AnswerInputEnum::Unknown;
    }
}
