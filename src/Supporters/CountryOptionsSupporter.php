<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\CountryResponseEntity;

class CountryOptionsSupporter
{
    /**
     * @param  Collection<int, CountryResponseEntity>  $countries
     * @return Collection<int, CountryResponseEntity>
     */
    public static function orderedForSelect(Collection $countries, ?string $fieldName = null): Collection
    {
        if (! self::shouldPrioritizeField($fieldName)) {
            return $countries->values();
        }

        $prioritizedIsoCodes = self::prioritizedIsoCodes();

        if ($prioritizedIsoCodes->isEmpty()) {
            return $countries->values();
        }

        $countriesByIsoCode = $countries->keyBy(
            fn (CountryResponseEntity $country): string => self::normalizeIsoCode($country->iso_code)
        );

        $prioritizedCountries = $prioritizedIsoCodes
            ->map(fn (string $isoCode): ?CountryResponseEntity => $countriesByIsoCode->get($isoCode))
            ->filter();

        $remainingCountries = $countries->reject(
            fn (CountryResponseEntity $country): bool => $prioritizedIsoCodes->contains(self::normalizeIsoCode($country->iso_code))
        );

        return $prioritizedCountries
            ->concat($remainingCountries)
            ->values();
    }

    private static function shouldPrioritizeField(?string $fieldName): bool
    {
        if ($fieldName === null) {
            return true;
        }

        return self::prioritizedFields()
            ->contains(self::normalizeFieldName($fieldName));
    }

    /**
     * @return Collection<int, non-empty-string>
     */
    private static function prioritizedIsoCodes(): Collection
    {
        return collect(config()->array('checkout.countries.prioritized_iso_codes'))
            ->map(fn (mixed $isoCode): string => self::normalizeIsoCode((string) $isoCode))
            ->filter(fn (string $isoCode): bool => $isoCode !== '')
            ->unique()
            ->map(fn (string $isoCode): string => $isoCode)
            ->values();
    }

    /**
     * @return Collection<int, non-empty-string>
     */
    private static function prioritizedFields(): Collection
    {
        return collect(config()->array('checkout.countries.prioritized_fields'))
            ->map(fn (mixed $fieldName): string => self::normalizeFieldName((string) $fieldName))
            ->filter(fn (string $fieldName): bool => $fieldName !== '')
            ->unique()
            ->map(fn (string $fieldName): string => $fieldName)
            ->values();
    }

    private static function normalizeFieldName(string $fieldName): string
    {
        return str($fieldName)->trim()->lower()->toString();
    }

    private static function normalizeIsoCode(string $isoCode): string
    {
        return str($isoCode)->trim()->upper()->toString();
    }
}
