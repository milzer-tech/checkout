<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class OnRequestResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the OnRequestResponseEntity.
     */
    public function __construct(
        public bool $confirmationEnabled = false,
        public ?string $confirmationText = null,
        public ?string $remarks = null,
    ) {

        $this->confirmationEnabled = true;
        $this->confirmationText = 'This is the text of confirmation.';
        $this->remarks = 'This is the text of remarks.';
    }

    /**
     * Get a stable key for the on-request acceptance based on displayed content.
     */
    public function getConfirmationKey(): string
    {
        return md5(json_encode([
            'confirmationText' => $this->confirmationText,
            'remarks' => $this->remarks,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
