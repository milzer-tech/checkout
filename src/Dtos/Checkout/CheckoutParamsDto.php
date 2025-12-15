<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Checkout;

use Nezasa\Checkout\Dtos\BaseDto;
use Spatie\LaravelData\Attributes\MapOutputName;

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
        #[MapOutputName('rest-payment')]
        public bool $restPayment = false
    ) {}

    /**
     * Map the DTO to a model-friendly array.
     *
     * @return array<string, string|null|bool>
     */
    public function mapToModel(): array
    {
        return [
            'checkout_id' => $this->checkoutId,
            'itinerary_id' => $this->itineraryId,
            'origin' => $this->origin,
            'lang' => $this->lang,
            'rest_payment' => $this->restPayment,
        ];
    }
}
