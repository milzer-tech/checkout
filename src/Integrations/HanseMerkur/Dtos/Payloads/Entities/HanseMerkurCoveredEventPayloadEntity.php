<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;

class HanseMerkurCoveredEventPayloadEntity extends BaseDto
{
    /**
     * Information concerning the trip or event of the offer or booking.
     *
     * @param  array<int, string>  $destinationCountries
     */
    public function __construct(
        public CarbonImmutable $bookingConfirmationDate,
        public CarbonImmutable $eventStartDate,
        public CarbonImmutable $eventEndDate,
        public HanseMerkurMoneyEntity $totalEventCost,
        public array $destinationCountries = [],
    ) {}
}
