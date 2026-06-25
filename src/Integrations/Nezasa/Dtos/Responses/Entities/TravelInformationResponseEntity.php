<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class TravelInformationResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the TravelInformationResponseEntity.
     */
    public function __construct(
        public bool $confirmationEnabled = false,
        public ?string $title = null,
        public ?string $intro = null,
        public ?string $checkboxText = null,
    ) {
        // todo: remove this after testing
        $this->confirmationEnabled = true;
        $this->title = 'Test: Travel Information';
        $this->intro = 'Test: Please confirm your travel information.';
        $this->checkboxText = 'Test: I confirm that the travel information provided is correct.';
    }
}
