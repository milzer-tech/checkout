<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoQuoteDto;

it('maps InsuranceDetails with a single InsuranceDetail child', function (): void {
    $dto = ErgoQuoteDto::from([
        'ID' => 1,
        'Services' => [
            'Service' => [],
        ],
        'InsuranceDetails' => [
            'InsuranceDetail' => [
                'Code' => 'SIT',
                'Title' => 'Einmalreise',
            ],
        ],
        'AcceptedPaymentTypes' => [],
    ]);

    expect($dto->InsuranceDetails)->toHaveCount(1)
        ->and($dto->InsuranceDetails->first()->Code)->toBe('SIT')
        ->and($dto->InsuranceDetails->first()->Title)->toBe('Einmalreise');
});

it('maps InsuranceDetails with multiple InsuranceDetail children', function (): void {
    $dto = ErgoQuoteDto::from([
        'ID' => 1,
        'Services' => [
            'Service' => [],
        ],
        'InsuranceDetails' => [
            'InsuranceDetail' => [
                ['Code' => 'A', 'Title' => 'First'],
                ['Code' => 'B', 'Title' => 'Second'],
            ],
        ],
        'AcceptedPaymentTypes' => [],
    ]);

    expect($dto->InsuranceDetails)->toHaveCount(2)
        ->and($dto->InsuranceDetails->last()->Title)->toBe('Second');
});
