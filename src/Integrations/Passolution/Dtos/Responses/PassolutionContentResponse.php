<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Dtos\Responses;

use Illuminate\Support\Arr;
use Nezasa\Checkout\Dtos\BaseDto;

class PassolutionContentResponse extends BaseDto
{
    private const SECTION_ALIASES = [
        'health' => ['health', 'healthInformation', 'health_info', 'vaccination', 'vaccinations'],
        'entry' => ['entry', 'entryRequirements', 'entry_requirements', 'conditions'],
        'visa' => ['visa', 'visaRequirements', 'visa_requirements'],
        'transit_visa' => ['transit', 'transitVisa', 'transitvisa', 'transitVisaRequirements', 'transit_visa_requirements'],
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload = [],
    ) {}

    /**
     * Create the DTO from the full API response without assuming a rigid vendor schema.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(payload: $payload);
    }

    public function forCombination(string $destinationCountryCode, string $nationalityCountryCode): self
    {
        $combinationPayload = $this->findCombinationPayload(
            payload: $this->payload,
            destinationCountryCode: $this->normalizeCountryValue($destinationCountryCode),
            nationalityCountryCode: $this->normalizeCountryValue($nationalityCountryCode),
        );

        return new self(payload: is_array($combinationPayload) ? $combinationPayload : $this->payload);
    }

    public function health(): ?string
    {
        return $this->sectionContent('health');
    }

    public function title(): ?string
    {
        $title = Arr::get($this->payload, 'title');

        return is_string($title) && trim($title) !== '' ? $title : null;
    }

    public function entryRequirements(): ?string
    {
        return $this->sectionContent('entry');
    }

    public function visaRequirements(): ?string
    {
        return $this->sectionContent('visa');
    }

    public function transitVisaRequirements(): ?string
    {
        return $this->sectionContent('transit_visa');
    }

    private function sectionContent(string $section): ?string
    {
        $aliases = self::SECTION_ALIASES[$section] ?? [$section];

        foreach ($aliases as $alias) {
            $value = Arr::get($this->payload, $alias);

            if ($value !== null) {
                return $this->stringify($value);
            }
        }

        $value = $this->findByAlias($this->payload, $aliases);

        return $value === null ? null : $this->stringify($value);
    }

    /**
     * @param  array<string|int, mixed>  $payload
     */
    private function findCombinationPayload(array $payload, string $destinationCountryCode, string $nationalityCountryCode): mixed
    {
        foreach ($payload as $key => $value) {
            if (is_array($value) && $this->keyMatchesCountry($key, $destinationCountryCode)) {
                $nestedByNationality = $this->findPayloadByCountryKey($value, $nationalityCountryCode);

                if (is_array($nestedByNationality)) {
                    return $nestedByNationality;
                }
            }

            if (! is_array($value)) {
                continue;
            }

            if ($this->payloadMatchesCombination($value, $destinationCountryCode, $nationalityCountryCode)) {
                return $value;
            }

            $nested = $this->findCombinationPayload($value, $destinationCountryCode, $nationalityCountryCode);

            if (is_array($nested)) {
                return $nested;
            }
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed>  $payload
     */
    private function findPayloadByCountryKey(array $payload, string $countryCode): mixed
    {
        foreach ($payload as $key => $value) {
            if (is_array($value) && $this->keyMatchesCountry($key, $countryCode)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed>  $payload
     */
    private function payloadMatchesCombination(array $payload, string $destinationCountryCode, string $nationalityCountryCode): bool
    {
        return $this->payloadHasCountryValue($payload, [
            'country',
            'countries',
            'destination',
            'destinationCountry',
            'destination_country',
            'destinationCountryCode',
            'destination_country_code',
        ], $destinationCountryCode)
            && $this->payloadHasCountryValue($payload, [
                'nat',
                'nationality',
                'nationalities',
                'nationalityCountry',
                'nationality_country',
                'nationalityCountryCode',
                'nationality_country_code',
            ], $nationalityCountryCode);
    }

    /**
     * @param  array<string|int, mixed>  $payload
     * @param  array<int, string>  $aliases
     */
    private function payloadHasCountryValue(array $payload, array $aliases, string $countryCode): bool
    {
        $normalizedAliases = array_map(fn (string $alias): string => $this->normalizeKey($alias), $aliases);

        foreach ($payload as $key => $value) {
            if (! is_string($key) || ! in_array($this->normalizeKey($key), $normalizedAliases, true)) {
                continue;
            }

            if ($this->countryValueMatches($value, $countryCode)) {
                return true;
            }
        }

        return false;
    }

    private function keyMatchesCountry(string|int $key, string $countryCode): bool
    {
        return is_string($key) && $this->normalizeCountryValue($key) === $countryCode;
    }

    private function countryValueMatches(mixed $value, string $countryCode): bool
    {
        if (is_string($value)) {
            return collect(explode(',', $value))
                ->map(fn (string $country): string => $this->normalizeCountryValue($country))
                ->contains($countryCode);
        }

        if (is_array($value)) {
            return collect($value)
                ->filter(fn (mixed $country): bool => is_string($country))
                ->map(fn (string $country): string => $this->normalizeCountryValue($country))
                ->contains($countryCode);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $aliases
     */
    private function findByAlias(array $payload, array $aliases): mixed
    {
        $normalizedAliases = array_map(fn (string $alias): string => $this->normalizeKey($alias), $aliases);

        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array($this->normalizeKey($key), $normalizedAliases, true)) {
                return $value;
            }

            if (is_array($value)) {
                $nested = $this->findByAlias($value, $aliases);

                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function stringify(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) !== '' ? $value : null;
        }

        if (! is_array($value)) {
            return null;
        }

        $parts = $this->extractTextParts($value);

        return $parts === [] ? null : implode("\n\n", $parts);
    }

    /**
     * @param  array<string|int, mixed>  $payload
     * @return array<int, string>
     */
    private function extractTextParts(array $payload): array
    {
        $parts = [];
        $preferredTextKeys = ['text', 'html', 'content', 'body', 'description', 'value'];

        foreach ($payload as $key => $value) {
            if (is_string($value) && trim($value) !== '') {
                if (is_int($key) || in_array($this->normalizeKey((string) $key), $preferredTextKeys, true)) {
                    $parts[] = $value;
                }
            }

            if (is_array($value)) {
                $parts = [...$parts, ...$this->extractTextParts($value)];
            }
        }

        return array_values(array_unique($parts));
    }

    private function normalizeKey(string $key): string
    {
        return str($key)->lower()->replace(['-', '_', ' '], '')->toString();
    }

    private function normalizeCountryValue(string $country): string
    {
        return str($country)
            ->trim()
            ->before('-')
            ->lower()
            ->toString();
    }
}
