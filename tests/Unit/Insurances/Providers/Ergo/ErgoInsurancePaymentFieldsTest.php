<?php

declare(strict_types=1);

use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;

it('declares IBAN as required payment data', function (): void {
    $fields = (new ErgoInsurance)->getPaymentFields();

    expect($fields)->toHaveCount(1)
        ->and($fields[0]->key)->toBe('iban')
        ->and($fields[0]->type)->toBe('iban')
        ->and($fields[0]->required)->toBeTrue();
});
