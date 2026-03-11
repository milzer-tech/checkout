<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurGenreEnum;

class HanseMerkurCustomerPayloadEntity extends BaseDto
{
    /**
     * Information concerning a participant of the trip or event to be insured.
     */
    public function __construct(
        // Country of residence of the insurance customer according to ISO 3166-1 alpha-2, for example, "DE"
        public HanseMerkurContactDataPayloadEntity $contactData,
        public string $countryOfResidence,
        public HanseMerkurGenreEnum $genre,

        public ?CarbonImmutable $birthDate = null,
        public ?string $givenName = null,
        public ?string $surname = null,

    ) {}
}
