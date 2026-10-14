<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Adyen\Support;

use Adyen\Model\Checkout\Amount;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

final class AdyenAmount
{
    /**
     * The decimals of the currencies that Adyen does not express in hundredths.
     *
     * @see https://docs.adyen.com/development-resources/currency-codes
     */
    private const array DECIMALS = [
        'BHD' => 3, 'IQD' => 3, 'JOD' => 3, 'KWD' => 3, 'LYD' => 3, 'OMR' => 3, 'TND' => 3,
        'CVE' => 0, 'DJF' => 0, 'GNF' => 0, 'IDR' => 0, 'JPY' => 0, 'KMF' => 0, 'KRW' => 0, 'PYG' => 0,
        'RWF' => 0, 'UGX' => 0, 'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0, 'XPF' => 0,
    ];

    /**
     * Get the amount of a price in the minor units of its currency, e.g. cents for EUR.
     */
    public static function fromPrice(Price $price): Amount
    {
        $currency = strtoupper($price->currency);

        return new Amount([
            'value' => (int) round($price->amount * 10 ** (self::DECIMALS[$currency] ?? 2)),
            'currency' => $currency,
        ]);
    }
}
