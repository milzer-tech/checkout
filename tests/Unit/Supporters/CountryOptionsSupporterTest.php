<?php

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\CountryResponseEntity;
use Nezasa\Checkout\Supporters\CountryOptionsSupporter;

it('pins configured countries above the original country order', function (): void {
    config()->set('checkout.countries.prioritized_iso_codes', ['DE', 'AT', 'CH']);
    config()->set('checkout.countries.prioritized_fields', ['nationality', 'country']);

    $country = fn (string $isoCode, string $name): CountryResponseEntity => new CountryResponseEntity(
        iso_code: $isoCode,
        name: $name,
        preferred: false,
    );

    $countries = collect([
        $country('AF', 'Afghanistan'),
        $country('EG', 'Ägypten'),
        $country('AL', 'Albanien'),
        $country('AT', 'Österreich'),
        $country('DE', 'Deutschland'),
        $country('CH', 'Schweiz'),
        $country('BR', 'Brasilien'),
    ]);

    expect(CountryOptionsSupporter::orderedForSelect($countries, 'nationality')->pluck('iso_code')->all())
        ->toBe(['DE', 'AT', 'CH', 'AF', 'EG', 'AL', 'BR']);

    expect(CountryOptionsSupporter::orderedForSelect($countries, 'country')->pluck('iso_code')->all())
        ->toBe(['DE', 'AT', 'CH', 'AF', 'EG', 'AL', 'BR']);
});

it('ignores missing or duplicate configured countries case-insensitively', function (): void {
    config()->set('checkout.countries.prioritized_iso_codes', [' ch ', 'XX', 'de', 'CH']);
    config()->set('checkout.countries.prioritized_fields', [' nationality ']);

    $country = fn (string $isoCode, string $name): CountryResponseEntity => new CountryResponseEntity(
        iso_code: $isoCode,
        name: $name,
        preferred: false,
    );

    $countries = collect([
        $country('AF', 'Afghanistan'),
        $country('AT', 'Österreich'),
        $country('DE', 'Deutschland'),
        $country('CH', 'Schweiz'),
    ]);

    expect(CountryOptionsSupporter::orderedForSelect($countries, 'Nationality')->pluck('iso_code')->all())
        ->toBe(['CH', 'DE', 'AF', 'AT']);
});

it('keeps the original country order for fields that are not configured', function (): void {
    config()->set('checkout.countries.prioritized_iso_codes', ['DE', 'AT', 'CH']);
    config()->set('checkout.countries.prioritized_fields', ['nationality']);

    $country = fn (string $isoCode, string $name): CountryResponseEntity => new CountryResponseEntity(
        iso_code: $isoCode,
        name: $name,
        preferred: false,
    );

    $countries = collect([
        $country('AF', 'Afghanistan'),
        $country('DE', 'Deutschland'),
        $country('AT', 'Österreich'),
        $country('CH', 'Schweiz'),
    ]);

    expect(CountryOptionsSupporter::orderedForSelect($countries, 'country')->pluck('iso_code')->all())
        ->toBe(['AF', 'DE', 'AT', 'CH']);
});

it('keeps the original country order when prioritization is not configured', function (): void {
    config()->set('checkout.countries.prioritized_iso_codes', ['']);
    config()->set('checkout.countries.prioritized_fields', ['']);

    $country = fn (string $isoCode, string $name): CountryResponseEntity => new CountryResponseEntity(
        iso_code: $isoCode,
        name: $name,
        preferred: false,
    );

    $countries = collect([
        $country('AF', 'Afghanistan'),
        $country('DE', 'Deutschland'),
        $country('AT', 'Österreich'),
        $country('CH', 'Schweiz'),
    ]);

    expect(CountryOptionsSupporter::orderedForSelect($countries, 'nationality')->pluck('iso_code')->all())
        ->toBe(['AF', 'DE', 'AT', 'CH']);
});
