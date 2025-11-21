<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\QuestionResponseEntity;

class ActivityQuestionResponse extends BaseDto
{
    /**
     * Create a new instance of the ActivityQuestionResponse
     *
     * @link https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Activities/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1activity-questions/get
     *
     * @param  Collection<int, QuestionResponseEntity>  $questions
     */
    public function __construct(
        public string $componentId,
        public string $productName,
        public Collection $questions,
    ) {}
}
