<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurGenreEnum;

class HanseMerkurInsuredPersonPayload extends BaseDto
{
    /**
     * Information concerning a participant of the trip or event to be insured.
     */
    public function __construct(
        public int $insuredPersonId,
        public CarbonImmutable $birthDate,
        public ?string $givenName = null,
        public ?string $surname = null,
        public ?HanseMerkurGenreEnum $gender = null,
    ) {}
}
