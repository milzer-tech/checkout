<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Adyen\Support\AdyenAmount;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

it('converts a price to the minor units Adyen uses for its currency', function (float $amount, string $currency, int $value, string $expectedCurrency): void {
    $adyenAmount = AdyenAmount::fromPrice(new Price($amount, $currency));

    expect($adyenAmount->getValue())->toBe($value)
        ->and($adyenAmount->getCurrency())->toBe($expectedCurrency);
})->with([
    'euro cents' => [199.9, 'EUR', 19990, 'EUR'],
    'dollar cents' => [1250.45, 'USD', 125045, 'USD'],
    'cents that a float truncation would lose' => [0.29, 'EUR', 29, 'EUR'],
    'more cents that a float truncation would lose' => [1.13, 'USD', 113, 'USD'],
    'large amount' => [1000000.99, 'EUR', 100000099, 'EUR'],
    'currency without decimals' => [15000.0, 'JPY', 15000, 'JPY'],
    'currency with three decimals' => [12.345, 'JOD', 12345, 'JOD'],
    'another currency with three decimals' => [7.5, 'KWD', 7500, 'KWD'],
    'currency Adyen expresses in hundredths unlike ISO 4217' => [100.0, 'CLP', 10000, 'CLP'],
    'lowercase currency' => [10.5, 'eur', 1050, 'EUR'],
]);
