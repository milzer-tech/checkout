<?php

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Supporters\InsuranceSupporter;

it('detects when exactly one insurance provider is active', function (): void {
    Config::set('checkout.insurance', [
        ['active' => false],
        ['active' => true],
    ]);

    expect(InsuranceSupporter::isAvailable())->toBeTrue();
});

it('returns false when no provider is active', function (): void {
    Config::set('checkout.insurance', [
        ['active' => false],
        ['active' => false],
    ]);

    expect(InsuranceSupporter::isAvailable())->toBeFalse();
});

it('throws when more than one provider is active', function (): void {
    Config::set('checkout.insurance', [
        ['active' => true],
        ['active' => true],
    ]);

    InsuranceSupporter::isAvailable();
})->throws(Exception::class, 'Only one insurance provider can be active at a time.');
