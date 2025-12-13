<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Checkout;

use Nezasa\Checkout\Dtos\BaseDto;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CheckoutParamsDto extends BaseDto
{
    /**
     * Create a new instance of CheckoutParamsDto.
     */
    public function __construct(
        public string $checkoutId,
        public string $itineraryId,
        public string $origin,
        public ?string $lang = null,
        public bool $restPayment = false
    ) {}
}
