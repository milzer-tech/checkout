<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

class AuthorizationResult extends BaseDto
{
    /**
     * Create a new instance of PaymentResult.
     *
     * @param  array<string, mixed>  $resultData
     */
    public function __construct(
        public bool $isSuccessful,
        public array $resultData = [],
    ) {}
}
