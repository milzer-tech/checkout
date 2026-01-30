<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

class CaptureResult extends BaseDto
{
    /**
     * Create a new instance of CaptureResult.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function __construct(
        public bool $isSuccessful,
        public array $persistentData = [],
    ) {}
}
