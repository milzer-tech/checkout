<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsuranceBookOfferResult extends BaseDto
{
    /**
     * Create a new instance of InsuranceBookOfferResult.
     *
     * @param  array<string|int, mixed>  $data
     */
    public function __construct(
        // Indicate if the booking was successful or not.
        public bool $isSuccessful,
        // the confirmation id is used to track the booking.
        public ?string $confirmationId = null,
        // This property's content is stored in the database and accessible for creating the NEZASA insurance payload.'
        public array $data = [],
    ) {}

}
