<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;

class PaymentOutput extends BaseDto
{
    /**
     * Create a new instance of PaymentOutput.
     *
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $gatewayName,
        public readonly bool $isNezasaBookingSuccessful,
        public readonly ?string $bookingReference = null,
        public readonly ?CarbonImmutable $orderDate = null,
        public array $data = [],
    ) {}
}
