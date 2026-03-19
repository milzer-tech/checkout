<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

final class CreateInsuranceOffersDto extends BaseDto
{
    /**
     * Create a new instance of CreateInsuranceOffersDto.
     *
     * @param  Collection<int, PaxInfoPayloadEntity>  $paxInfo
     * @param  Collection<int, string>  $destinationCountries
     */
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public Price $totalPrice,
        public ContactInfoPayloadEntity $contact,
        public Collection $paxInfo,
        public Collection $destinationCountries,
    ) {}

    /**
     * @param  array<string|int, mixed>  ...$payloads
     */
    public static function from(...$payloads): static
    {
        if (isset($payloads[0]['destinationCountries']) && is_array($payloads[0]['destinationCountries'])) {
            $payloads[0]['destinationCountries'] = collect($payloads[0]['destinationCountries']);
        }

        return parent::from(...$payloads);
    }
}
