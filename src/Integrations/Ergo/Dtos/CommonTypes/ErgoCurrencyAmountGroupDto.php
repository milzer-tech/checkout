<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Data;

class ErgoCurrencyAmountGroupDto extends Data
{
    /**
     * @param  int  $DecimalPlaces  Indicates the number of decimal places for a particular currency. This is
     *                              equivalent to the ISO 4217 standard "minor unit". Typically used when the
     *                              amount provided includes the minor unit of currency without a decimal point
     *                              (e.g., USD 8500 needs DecimalPlaces="2" to represent $85).
     */
    public function __construct(
        public string $Amount,
        public string $CurrencyCode,
    ) {}
}
