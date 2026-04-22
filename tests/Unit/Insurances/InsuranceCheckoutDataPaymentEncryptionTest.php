<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Crypt;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;

it('encrypts the full payment map as one envelope and getPayment returns plaintext', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'offer-1',
        'title' => 'Test',
        'price' => ['amount' => 10.0, 'currency' => 'EUR'],
        'coverage' => [],
    ];
    $plainIban = 'DE89370400440532013000';
    $bucket[InsuranceCheckoutData::PAYMENT] = ['iban' => $plainIban];

    $update = InsuranceCheckoutData::prepareInsuranceUpdate($bucket);
    $storedPayment = $update['insurance'][InsuranceCheckoutData::PAYMENT];

    expect($storedPayment)->toBeArray()
        ->and(array_keys($storedPayment))->toHaveCount(1)
        ->and($storedPayment)->not->toHaveKey('iban');

    $encoded = json_encode($storedPayment);
    expect($encoded)->not->toContain($plainIban);

    $checkout = ['insurance' => $update['insurance']];
    expect(InsuranceCheckoutData::getPayment($checkout)['iban'] ?? null)->toBe($plainIban);
});

it('does not re-wrap an already persisted payment envelope when saving again', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::PAYMENT] = ['iban' => 'DE89370400440532013000'];
    $once = InsuranceCheckoutData::prepareInsuranceUpdate($bucket);
    $stored = $once['insurance'][InsuranceCheckoutData::PAYMENT];

    $bucket2 = $once['insurance'];
    $twice = InsuranceCheckoutData::prepareInsuranceUpdate($bucket2);

    expect($twice['insurance'][InsuranceCheckoutData::PAYMENT])->toBe($stored);
});

it('encrypts every payment field inside the same envelope', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::PAYMENT] = [
        'iban' => 'DE89370400440532013000',
        'account_holder' => 'Jane Doe',
        'bic' => 'COBADEFFXXX',
    ];

    $update = InsuranceCheckoutData::prepareInsuranceUpdate($bucket);
    $raw = json_encode($update['insurance'][InsuranceCheckoutData::PAYMENT]);

    expect($raw)->not->toContain('Jane Doe')
        ->and($raw)->not->toContain('COBADEFFXXX')
        ->and($raw)->not->toContain('DE89370400440532013000');

    $plain = InsuranceCheckoutData::getPayment(['insurance' => $update['insurance']]);
    expect($plain['iban'] ?? null)->toBe('DE89370400440532013000')
        ->and($plain['account_holder'] ?? null)->toBe('Jane Doe')
        ->and($plain['bic'] ?? null)->toBe('COBADEFFXXX');
});

it('getPayment returns legacy plaintext insurance_payment unchanged', function (): void {
    $plainIban = 'DE89370400440532013000';
    $checkout = [
        'insurance' => InsuranceCheckoutData::emptyInsuranceBucket(),
        'insurance_payment' => ['iban' => $plainIban],
    ];

    expect(InsuranceCheckoutData::getPayment($checkout)['iban'] ?? null)->toBe($plainIban);
});

it('migrates legacy per-iban ciphertext to full envelope on save', function (): void {
    $plainIban = 'DE89370400440532013000';
    $legacyV1 = [
        'iban' => Crypt::encryptString($plainIban),
    ];
    $checkout = [
        'insurance' => [
            ...InsuranceCheckoutData::emptyInsuranceBucket(),
            InsuranceCheckoutData::PAYMENT => $legacyV1,
        ],
    ];

    expect(InsuranceCheckoutData::getPayment($checkout)['iban'] ?? null)->toBe($plainIban);

    $bucket = InsuranceCheckoutData::getNormalizedInsuranceBucket($checkout);
    expect($bucket)->not->toBeNull();
    $update = InsuranceCheckoutData::prepareInsuranceUpdate($bucket);
    $stored = $update['insurance'][InsuranceCheckoutData::PAYMENT];

    expect(array_keys($stored))->toHaveCount(1);
    expect(InsuranceCheckoutData::getPayment(['insurance' => $update['insurance']])['iban'] ?? null)->toBe($plainIban);
});
