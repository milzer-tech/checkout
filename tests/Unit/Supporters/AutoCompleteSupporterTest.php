<?php

use Nezasa\Checkout\Supporters\AutoCompleteSupporter;

it('returns autocomplete token when field is mapped', function (): void {
    expect(AutoCompleteSupporter::get('firstName'))->toBe('autocomplete=given-name')
        ->and(AutoCompleteSupporter::get('postalCode'))->toBe('autocomplete=postal-code');
});

it('returns empty string for unknown fields', function (): void {
    expect(AutoCompleteSupporter::get('unknown'))->toBe('');
});
