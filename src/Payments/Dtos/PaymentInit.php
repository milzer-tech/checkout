<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Illuminate\Support\Uri;
use Nezasa\Checkout\Dtos\BaseDto;

class PaymentInit extends BaseDto
{
    /**
     * Create a new instance of PaymentInit.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function __construct(
        public bool $isAvailable,
        public Uri $returnUrl,
        // This property's content is stored in the database.
        public array $persistentData = [],
    ) {}
}
