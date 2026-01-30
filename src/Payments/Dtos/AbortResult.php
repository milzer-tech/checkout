<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

class AbortResult extends BaseDto
{
    /**
     * Create a new instance of AbortResult.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function __construct(
        public bool $isSuccessful,
        public array $persistentData = [],
    ) {}
}
